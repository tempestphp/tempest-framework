<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Casters\CasterFactory;
use Tempest\Mapper\Exceptions\MissingValuesException;
use Tempest\Mapper\MapFrom;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\Strict;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Exceptions\PropertyValidationException;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Validator;
use Throwable;
use function Tempest\Support\arr;

final readonly class ArrayToObjectMapper implements Mapper
{
    public function __construct(
        private CasterFactory $casterFactory,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        if (! is_array($from)) {
            return false;
        }

        try {
            $class = new ClassReflector($to);

            return $class->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }

    public function map(mixed $from, mixed $to): object
    {
        $validator = new Validator();

        $class = new ClassReflector($to);

        $object = $this->resolveObject($to);

        $missingValues = [];

        /** @var PropertyReflector[] $unsetProperties */
        $unsetProperties = [];

        /** @var \Tempest\Validation\Rule[] $failingRules */
        $failingRules = [];

        $from = arr($from)->unwrap()->toArray();

        $isStrictClass = $class->hasAttribute(Strict::class);

        foreach ($class->getPublicProperties() as $property) {
            if ($property->isVirtual()) {
                continue;
            }

            $propertyName = $this->resolvePropertyName($property, $from);

            if (! array_key_exists($propertyName, $from)) {
                $isStrictProperty = $isStrictClass || $property->hasAttribute(Strict::class);

                if ($property->hasDefaultValue()) {
                    continue;
                }

                if ($isStrictProperty) {
                    $missingValues[] = $propertyName;
                } else {
                    $unsetProperties[] = $property;
                }

                continue;
            }

            $value = $this->resolveValue($property, $from[$propertyName]);

            try {
                $validator->validateProperty($property, $value);
            } catch (PropertyValidationException $propertyValidationException) {
                $failingRules[$property->getName()] = $propertyValidationException->failingRules;
                continue;
            }

            $property->setValue($object, $value);
        }

        if ($failingRules !== []) {
            throw new ValidationException($object, $failingRules);
        }

        if ($missingValues !== []) {
            throw new MissingValuesException($to, $missingValues);
        }

        $this->setParentRelations($object, $class);

        // Non-strict properties that weren't passed are unset,
        // which means that they can now be accessed via `__get`
        foreach ($unsetProperties as $property) {
            if ($property->isVirtual()) {
                continue;
            }

            $property->unset($object);
        }

        return $object;
    }

    /**
     * @param array<mixed> $from
     */
    private function resolvePropertyName(PropertyReflector $property, array $from): string
    {
        $mapFrom = $property->getAttribute(MapFrom::class);

        if ($mapFrom !== null) {
            return arr($from)->keys()->intersect($mapFrom->names)->first() ?? $property->getName();
        }

        return $property->getName();
    }

    private function resolveObject(mixed $objectOrClass): object
    {
        if (is_object($objectOrClass)) {
            return $objectOrClass;
        }

        return new ClassReflector($objectOrClass)->newInstanceWithoutConstructor();
    }

    private function setParentRelations(
        object $parent,
        ClassReflector $parentClass,
    ): void
    {
        foreach ($parentClass->getPublicProperties() as $property) {
            if (! $property->isInitialized($parent)) {
                continue;
            }

            if ($property->isVirtual()) {
                continue;
            }

            $type = $property->getIterableType() ?? $property->getType();

            if (! $type?->isClass()) {
                continue;
            }

            $child = $property->getValue($parent);

            if ($child === null) {
                continue;
            }

            $childClass = $type->asClass();

            foreach ($childClass->getPublicProperties() as $childProperty) {
                // Determine the value to set in the child property
                if ($childProperty->getType()->equals($parent::class)) {
                    $valueToSet = $parent;
                } elseif ($childProperty->getIterableType()?->equals($parent::class)) {
                    $valueToSet = [$parent];
                } else {
                    continue;
                }

                if (is_array($child)) {
                    // Set the value for each child element if the child is an array
                    foreach ($child as $childItem) {
                        lw($childItem);
                        $childProperty->setValue($childItem, $valueToSet);
                    }
                } else {
                    // Set the value directly on the child element if it's an object
                    $childProperty->setValue($child, $valueToSet);
                }
            }
        }
    }

    public function resolveValue(PropertyReflector $property, mixed $value): mixed
    {
        // If this isn't a property with iterable type defined, and the type accepts the value, we don't have to cast it
        // We need to check the iterable type, because otherwise raw array input might incorrectly be seen as "accepted by the property's array type",
        // which isn't sufficient a check.
        // Oh how we long for the day that PHP gets generics…
        if ($property->getIterableType() === null && $property->getType()->accepts($value)) {
            return $value;
        }

        // If there is an iterable type, and it accepts the value within the array given, we don't have to cast it either
        if ($property->getIterableType()?->accepts(arr($value)->first())) {
            return $value;
        }

        // If there's a caster, we'll cast the value
        if (($caster = $this->casterFactory->forProperty($property)) !== null) {
            return $caster->cast($value);
        }

        // Otherwise we'll return the value as-is
        return $value;
    }
}
