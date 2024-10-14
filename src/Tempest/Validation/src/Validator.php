<?php

declare(strict_types=1);

namespace Tempest\Validation;

use Closure;
use Tempest\Reflection\ClassReflector;
use function Tempest\Support\arr;
use Tempest\Validation\Exceptions\InvalidValueException;
use Tempest\Validation\Exceptions\ValidationException;

final readonly class Validator
{
    public function validate(object $object): void
    {
        $class = new ClassReflector($object);

        $failingRules = [];

        foreach ($class->getPublicProperties() as $property) {
            $rules = $property->getAttributes(Rule::class);

            if (! $property->isInitialized($object)) {
                continue;
            }

            $value = $property->getValue($object);

            try {
                $this->validateValue($value, $rules);
            } catch (InvalidValueException $invalidValueException) {
                $failingRules[$property->getName()] = $invalidValueException->failingRules;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($object, $failingRules);
        }
    }

    /**
     * @param Rule[] $rules
     */
    public function validateValue(mixed $value, Closure|Rule|array $rules): void
    {
        $failingRules = [];

        foreach (arr($rules) as $rule) {
            if ($rule instanceof Closure) {
                $result = $rule($value);

                [$isValid, $message] = match (true) {
                    is_string($result) => [false, $result],
                    $result === false => [false, 'Value did not pass validation.'],
                    default => [true, ''],
                };

                $rule = new class ($isValid, $message) implements Rule {
                    public function __construct(
                        private readonly bool $isValid,
                        private readonly string $message,
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

            $isValid = $rule->isValid($value);

            if (! $isValid) {
                $failingRules[] = $rule;
            }
        }

        if ($failingRules !== []) {
            throw new InvalidValueException($value, $failingRules);
        }
    }
}
