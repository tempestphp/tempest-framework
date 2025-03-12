<?php

declare(strict_types=1);

namespace Tempest\Validation;

use Closure;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Exceptions\InvalidValueException;
use Tempest\Validation\Exceptions\PropertyValidationException;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsFloat;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Rules\NotEmpty;
use Tempest\Validation\Rules\NotNull;

use Throwable;
use function Tempest\Support\arr;

final readonly class Validator
{
    public function validateObject(object $object): void
    {
        $class = new ClassReflector($object);

        $failingRules = [];

        foreach ($class->getPublicProperties() as $property) {
            if (! $property->isInitialized($object)) {
                continue;
            }

            $value = $property->getValue($object);

            try {
                $this->validateValueForProperty($property, $value);
            } catch (PropertyValidationException $invalidValueException) {
                $failingRules[$property->getName()] = $invalidValueException->failingRules;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($object, $failingRules);
        }
    }

    public function validateValueForProperty(PropertyReflector $property, mixed $value, string $prefix = ''): array
    {
        $failingRules = [];

        if ($property->getType()->isClass()) {
            foreach ($property->getType()->asClass()->getPublicProperties() as $subProperty) {
                $failingRules[ltrim($prefix . '.' . $property->getName() . '.' . $subProperty->getName(), '.')] = $this->validateValueForProperty($subProperty, $value, ltrim($prefix . '.' . $property->getName(), '.'));
            }
        }

        $rules = $property->getAttributes(Rule::class);

        if (! $property->isNullable()) {
            $rules[] = new NotNull();
        }

        if ($property->getType()->isScalar()) {
            $rules[] = match ($property->getType()->getName()) {
                'string' => new IsString(orNull: $property->isNullable()),
                'int' => new IsInteger(orNull: $property->isNullable()),
                'float' => new IsFloat(orNull: $property->isNullable()),
                'bool' => new IsBoolean(orNull: $property->isNullable()),
                default => null,
            };
        }


        return [...$failingRules, ...$this->validateValue($value, $rules)];
    }

    public function validateValue(mixed $value, Closure|Rule|array $rules): array
    {
        $failingRules = [];

        foreach (arr($rules) as $rule) {
            if (! $rule) {
                continue;
            }

            $rule = $this->convertToRule($rule, $value);

            if (! $rule->isValid($value)) {
                $failingRules[] = $rule;
            }
        }

        return $failingRules;
    }

    private function convertToRule(Rule|Closure $rule, mixed $value): Rule
    {
        if ($rule instanceof Rule) {
            return $rule;
        }

        $result = $rule($value);

        [$isValid, $message] = match (true) {
            is_string($result) => [false, $result],
            $result === false => [false, 'Value did not pass validation.'],
            default => [true, ''],
        };

        return new readonly class($isValid, $message) implements Rule {
            public function __construct(
                private bool $isValid,
                private string $message,
            ) {}

            public function isValid(mixed $value): bool
            {
                return $this->isValid;
            }

            public function message(): string
            {
                return $this->message;
            }
        };
    }
}
