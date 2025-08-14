<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\CasterFactory;
use Tempest\Mapper\Exceptions\MappingValuesWereMissing;
use Tempest\Mapper\MapFrom;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\Strict;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
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
            return new ClassReflector($to)->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }

    public function map(mixed $from, mixed $to): object
    {
        $class = new ClassReflector($to);
        $object = $this->resolveObject($to);
        $from = arr($from)->undot()->toArray();
        $isStrictClass = $class->hasAttribute(Strict::class);

        $missingValues = [];
        $unsetProperties = [];

        foreach ($class->getPublicProperties() as $property) {
            if ($property->isVirtual()) {
                continue;
            }

            $propertyName = $this->resolvePropertyName($property, $from);

            if (! array_key_exists($propertyName, $from)) {
                $this->handleMissingProperty(
                    property: $property,
                    propertyName: $propertyName,
                    isStrictClass: $isStrictClass,
                    missingValues: $missingValues,
                    unsetProperties: $unsetProperties,
                );
                continue;
            }

            $value = $this->resolveValue($property, $from[$propertyName]);
            $property->setValue($object, $value);
        }

        if ($missingValues !== []) {
            throw new MappingValuesWereMissing($to, $missingValues);
        }

        $this->setParentRelations($object, $class);

        foreach ($unsetProperties as $property) {
            if ($property->isVirtual()) {
                continue;
            }

            $property->unset($object);
        }

        return $object;
    }

    private function resolvePropertyName(PropertyReflector $property, array $from): string
    {
        $mapFrom = $property->getAttribute(MapFrom::class);

        if ($mapFrom !== null) {
            return arr($from)
                ->keys()
                ->intersect($mapFrom->names)
                ->first(default: $property->getName());
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

    private function setParentRelations(object $parent, ClassReflector $parentClass): void
    {
        foreach ($parentClass->getPublicProperties() as $property) {
            if (! $property->isInitialized($parent) || $property->isVirtual()) {
                continue;
            }

            $type = $property->getIterableType() ?? $property->getType();

            if (! $type->isClass()) {
                continue;
            }

            $child = $property->getValue($parent);

            if ($child === null) {
                continue;
            }

            $this->setChildParentRelation($parent, $child, $type->asClass());
        }
    }

    private function setChildParentRelation(object $parent, mixed $child, ClassReflector $childClass): void
    {
        foreach ($childClass->getPublicProperties() as $childProperty) {
            if ($childProperty->getType()->equals($parent::class)) {
                $valueToSet = $parent;
            } elseif ($childProperty->getIterableType()?->equals($parent::class)) {
                $valueToSet = [$parent];
            } else {
                continue;
            }

            if (is_array($child)) {
                foreach ($child as $childItem) {
                    $childProperty->setValue($childItem, $valueToSet);
                }
            } else {
                $childProperty->setValue($child, $valueToSet);
            }
        }
    }

    public function resolveValue(PropertyReflector $property, mixed $value): mixed
    {
        $caster = $this->casterFactory->forProperty($property);

        if ($caster === null) {
            return $value;
        }

        if ($property->getIterableType() !== null) {
            return $caster->cast($value);
        }

        if (! $property->getType()->accepts($value)) {
            return $caster->cast($value);
        }

        if (is_object($value) && $property->getType()->matches($value::class)) {
            return $value;
        }

        return $caster->cast($value);
    }

    private function handleMissingProperty(
        PropertyReflector $property,
        string $propertyName,
        bool $isStrictClass,
        array &$missingValues,
        array &$unsetProperties,
    ): void {
        if ($property->hasDefaultValue()) {
            return;
        }

        $isStrictProperty = $isStrictClass || $property->hasAttribute(Strict::class);

        if ($isStrictProperty) {
            $missingValues[] = $propertyName;
        } else {
            $unsetProperties[] = $property;
        }
    }
}
