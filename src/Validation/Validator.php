<?php

declare(strict_types=1);

namespace Tempest\Validation;

use ReflectionClass;
use ReflectionProperty;
use function Tempest\attribute;
use Tempest\Validation\Exceptions\ValidationException;

final readonly class Validator
{
    public function __construct()
    {
    }

    public function validate(object $object): void
    {
        $class = new ReflectionClass($object);

        $failingRules = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $rules = attribute(Rule::class)->in($property)->all();

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
