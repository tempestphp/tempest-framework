<?php

declare(strict_types=1);

namespace Tempest\Validation;

use ReflectionClass;
use ReflectionProperty;
use function Tempest\attribute;
use Tempest\Console\Argument;
use Tempest\Validation\Exceptions\ValidationException;

// todo: just a proof of concept.
final readonly class NestedValidator implements Validator
{
    public function validate(mixed $object): void
    {
        $class = new ReflectionClass($object);

        $failingRules = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $rules = attribute(Rule::class)->in($property)->all();

            if (! $property->isInitialized($object)) {
                continue;
            }

            $value = $property->getValue($object);

            if ($property->getType()->isBuiltin()) {
                $rule = $this->getRuleByType($property->getType()->getName());

                if ($rule) {
                    $rules[] = $rule;
                }
            }

            if (is_iterable($value)) {
                foreach ($value as $item) {
                    if ($item instanceof Argument) {
                        $this->validateNested($item, $failingRules);
                    }
                }
            }

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

    protected function validateNested(object $object, &$failingRules): void
    {
        if (! $object instanceof Argument || ! $object->parameter) {
            return;
        }

        $rules = attribute(Rule::class)->in($object->parameter)->all();

        if ($object->parameter->getType()->isBuiltin()) {
            $rule = $this->getRuleByType($object->parameter->getType()->getName());

            if ($rule) {
                $rules[] = $rule;
            }
        }

        $value = $object;

        foreach ($rules as $rule) {
            $isValid = $rule->isValid($value->getValue() ?? $value->getDefaultValue());

            if (! $isValid) {
                $failingRules[$object->name][] = $rule;
            }
        }
    }

    private function getRuleByType(string $getName): ?Rule
    {
        return match ($getName) {
            //            'int' => new Rules\IntegerRule(),
            //            'string' => new Rules\StringRule(),
            //            'float' => new Rules\FloatRule(),
            'bool' => new Rules\Boolean(),
            //            'array' => new Rules\ArrayRule(),
            default => null,
        };
    }
}
