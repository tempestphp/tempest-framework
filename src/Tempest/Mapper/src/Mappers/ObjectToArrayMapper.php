<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use JsonSerializable;
use ReflectionException;
use Tempest\Mapper\Casters\CasterFactory;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo as MapToAttribute;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use function Tempest\Support\arr;

final readonly class ObjectToArrayMapper implements Mapper
{
    public function __construct(
        private CasterFactory $casterFactory,
    ) {
    }

    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): array
    {
        $properties = $this->resolveProperties($from);
        $mappedProperties = [];

        foreach ($properties as $propertyName => $propertyValue) {
            try {
                $property = PropertyReflector::fromParts(class: $from, name: $propertyName);
                $propertyName = $this->resolvePropertyName($property);
                $propertyValue = $this->resolvePropertyValue($property, $propertyValue);

                $mappedProperties[$propertyName] = $propertyValue;
            } catch (ReflectionException) {
                continue;
            }
        }

        return $mappedProperties;
    }

    private function resolvePropertyValue(PropertyReflector $property, mixed $currentPropertyValue): mixed
    {
        $caster = $this->casterFactory->forProperty($property);

        return $caster?->serialize($currentPropertyValue) ?? $currentPropertyValue;
    }

    /**
     * @return array<string, mixed> The properties name and value
     */
    private function resolveProperties(object $from): array
    {
        if ($from instanceof JsonSerializable) {
            return $from->jsonSerialize();
        }

        try {
            $class = new ClassReflector($from);
            $properties = $class->getPublicProperties();

            return arr(iterator_to_array($properties))
                ->mapWithKeys(fn (PropertyReflector $property) => yield $property->getName() => $property->getValue($from))
                ->toArray();
        } catch (ReflectionException) {
            return [];
        }
    }

    private function resolvePropertyName(PropertyReflector $property): string
    {
        $mapTo = $property->getAttribute(MapToAttribute::class);

        if ($mapTo !== null) {
            return $mapTo->name;
        }

        return $property->getName();
    }
}
