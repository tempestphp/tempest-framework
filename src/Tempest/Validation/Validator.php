<?php

declare(strict_types=1);

namespace Tempest\Validation;

use ReflectionClass;
use ReflectionProperty;
use Tempest\Support\Reflection\Attributes;
use Tempest\Validation\Exceptions\InvalidValueException;
use Tempest\Validation\Exceptions\ValidationException;

final readonly class Validator
{
    public function validate(object $object): void
    {
        $class = new ReflectionClass($object);

        $failingRules = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $rules = Attributes::find(Rule::class)->in($property)->all();

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
    public function validateValue(mixed $value, array $rules): void
    {
        $failingRules = [];

        foreach ($rules as $rule) {
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
