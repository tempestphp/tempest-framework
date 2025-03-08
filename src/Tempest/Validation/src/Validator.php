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
                $this->validateProperty($property, $value);
            } catch (PropertyValidationException $invalidValueException) {
                $failingRules[$property->getName()] = $invalidValueException->failingRules;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($object, $failingRules);
        }
    }

    public function validateProperty(PropertyReflector $property, mixed $value): void
    {
        $failingRules = [];

        try {
            $rules = $property->getAttributes(Rule::class);

            if (! $property->isNullable()) {
                $rules[] = new NotNull();
            }

            if ($property->getType()?->isScalar()) {
                $rules[] = match ($property->getType()->getName()) {
                    'string' => new IsString(),
                    'int' => new IsInteger(),
                    'float' => new IsFloat(),
                    'bool' => new IsBoolean(),
                };
            }

            $this->validateValue($value, $rules);
        } catch (InvalidValueException $invalidValueException) {
            $failingRules = $invalidValueException->failingRules;
        }

        if ($failingRules !== []) {
            throw new PropertyValidationException($property, $failingRules);
        }
    }

    public function validateValue(mixed $value, Closure|Rule|array $rules): void
    {
        $failingRules = [];

        foreach (arr($rules) as $rule) {
            $rule = $this->convertToRule($rule, $value);

            if (! $rule->isValid($value)) {
                $failingRules[] = $rule;
            }
        }

        if ($failingRules !== []) {
            throw new InvalidValueException($value, $failingRules);
        }
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

        return new readonly class ($isValid, $message) implements Rule {
            public function __construct(
                private bool $isValid,
                private string $message,
            ) {
            }

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
