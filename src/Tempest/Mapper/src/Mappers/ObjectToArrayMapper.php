<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use JsonSerializable;
use ReflectionException;
use Tempest\Mapper\Casters\CasterFactory;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Tempest\Mapper\Serializers\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;

use function Tempest\Support\arr;

final readonly class ObjectToArrayMapper implements Mapper
{
    public function __construct(
        private SerializerFactory $serializerFactory,
    ) {
    }

    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): array
    {
        if ($from instanceof JsonSerializable) {
            return $from->jsonSerialize();
        }

        $class = new ClassReflector($from);

        $mappedProperties = [];

        foreach ($class->getPublicProperties() as $property) {
            $propertyName = $this->resolvePropertyName($property);
            $propertyValue = $this->resolvePropertyValue($property, $from);
            $mappedProperties[$propertyName] = $propertyValue;
        }

        return $mappedProperties;
    }

    private function resolvePropertyValue(PropertyReflector $property, object $object): mixed
    {
        $propertyValue = $property->getValue($object);

        if (($serializer = $this->serializerFactory->forProperty($property)) !== null) {
            return $serializer->serialize($propertyValue);
        }

        return $propertyValue;
    }

    private function resolvePropertyName(PropertyReflector $property): string
    {
        $mapTo = $property->getAttribute(MapTo::class);

        if ($mapTo !== null) {
            return $mapTo->name;
        }

        return $property->getName();
    }
}
