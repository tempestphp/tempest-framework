<?php

declare(strict_types=1);

namespace Tempest\Validation;

use Closure;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsFloat;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Rules\NotNull;
use TypeError;

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

            $failingRules[$property->getName()] = $this->validateValueForProperty($property, $value);
        }

        if ($failingRules !== []) {
            throw new ValidationException($object, $failingRules);
        }
    }

    /**
     * @param ClassReflector|class-string $class
     */
    public function validateValuesForClass(ClassReflector|string $class, ?array $values, string $prefix = ''): array
    {
        $class = is_string($class) ? new ClassReflector($class) : $class;

        $failingRules = [];

        $values = arr($values)->undot();

        foreach ($class->getPublicProperties() as $property) {
            if ($property->hasAttribute(SkipValidation::class)) {
                continue;
            }

            $key = $prefix . $property->getName();

            if (! $values->has($key) && $property->hasDefaultValue()) {
                continue;
            }

            $value = $values->get($key);

            $failingRulesForProperty = $this->validateValueForProperty($property, $value);

            if ($failingRulesForProperty !== []) {
                $failingRules[$key] = $failingRulesForProperty;
            }

            if ($property->isNullable() && $value === null) {
                continue;
            }

            if ($property->getType()->isClass()) {
                $failingRules = [
                    ...$failingRules,
                    ...$this->validateValuesForClass(
                        class: $property->getType()->asClass(),
                        values: $values->dot()->toArray(),
                        prefix: $key . '.',
                    ),
                ];
            }
        }

        return $failingRules;
    }

    public function validateValueForProperty(PropertyReflector $property, mixed $value): array
    {
        $rules = $property->getAttributes(Rule::class);

        if ($property->getType()->isScalar()) {
            $rules[] = match ($property->getType()->getName()) {
                'string' => new IsString(orNull: $property->isNullable()),
                'int' => new IsInteger(orNull: $property->isNullable()),
                'float' => new IsFloat(orNull: $property->isNullable()),
                'bool' => new IsBoolean(orNull: $property->isNullable()),
                default => null,
            };
        } elseif (! $property->isNullable()) {
            // We only add the NotNull rule if we're not dealing with scalar types, since the null check is included in the scalar rules
            $rules[] = new NotNull();
        }

        return $this->validateValue($value, $rules);
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
