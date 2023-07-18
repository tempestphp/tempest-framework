<?php

declare(strict_types=1);

namespace Tempest\Validation;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Tempest\Interfaces\IsValidated;
use Tempest\Interfaces\Rule;
use Tempest\Validation\Exceptions\ValidationException;

final readonly class Validator
{
    public function validate(IsValidated $object): void
    {
        $class = new ReflectionClass($object);

        $failingRules = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            /** @var \Tempest\Interfaces\Rule[] $rules */
            $rules = array_map(
                fn (ReflectionAttribute $attribute) => $attribute->newInstance(),
                $property->getAttributes(Rule::class, ReflectionAttribute::IS_INSTANCEOF),
            );

            if (! $property->isInitialized($object)) {
                continue;
            }

            $value = $property->getValue($object);

            foreach ($rules as $rule) {
                $isValid = $rule->isValid($value);

                if (! $isValid) {
                    $failingRules[$property->getName()][] = $rule;
                }
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($object, $failingRules);
        }
    }
}
