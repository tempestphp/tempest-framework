<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use JsonSerializable;
use ReflectionException;
use Tempest\Mapper\Attributes\MapTo as MapToAttribute;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use function Tempest\Support\arr;

final readonly class ObjectToArrayMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === MapTo::ARRAY && is_object($from);
    }

    public function map(mixed $from, mixed $to): array
    {
        $properties = $this->resolveProperties($from);
        $mappedProperties = [];

        foreach ($properties as $property_name => $property_value) {
            try {
                $property = PropertyReflector::fromParts(class: $from, name: $property_name);
                $property_name = $this->resolvePropertyName($property);
                $property_value = $property_value;

                // @TODO May be removed if we want to handle private/protected properties
                if (! $property->isPublic()) {
                    continue;
                }

                $mappedProperties[ $property_name ] = $property_value;
            } catch (ReflectionException) {
                continue;
            }
        }

        return $mappedProperties;
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
            $properties = $class->getProperties();
            $properties = iterator_to_array($properties);

            return arr($properties)
                ->mapWithKeys(fn (PropertyReflector $property) => yield $property->getName() => $property->getValue($from))
                ->toArray();
        } catch (ReflectionException) {
            return [];
        }
    }

    private function resolvePropertyName(PropertyReflector $property): string
    {
        $property_name = $property->getName();
        $property_mapto_attribute = $property->getAttribute(MapToAttribute::class);

        if (! is_null($property_mapto_attribute)) {
            $property_name = $property_mapto_attribute->name;
        }

        return $property_name;
    }
}
