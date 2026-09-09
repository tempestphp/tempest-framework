<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use JsonSerializable;
use Tempest\Mapper\Context;
use Tempest\Mapper\Hidden;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Tempest\Mapper\Serializer;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;

final readonly class ObjectToArrayMapper implements Mapper
{
    public function __construct(
        private SerializerFactory $serializerFactory,
        private Context $context,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): mixed
    {
        $visited = [];

        return $this->mapValue($from, $visited);
    }

    /**
     * @param array<int, true> $visited
     */
    private function mapValue(mixed $value, array &$visited): mixed
    {
        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        if (! is_object($value)) {
            return $value;
        }

        return $this->mapObject($value, $visited);
    }

    /**
     * @param array<int, true> $visited
     */
    private function mapObject(object $object, array &$visited): mixed
    {
        $objectId = spl_object_id($object);

        if (isset($visited[$objectId])) {
            return $object;
        }

        $visited[$objectId] = true;

        $class = new ClassReflector($object);

        $mappedProperties = [];

        foreach ($class->getPublicProperties() as $property) {
            if ($property->hasAttribute(Hidden::class)) {
                continue;
            }

            $propertyName = $this->resolvePropertyName($property);
            $propertyValue = $this->resolvePropertyValue($property, $object, $visited);
            $mappedProperties[$propertyName] = $propertyValue;
        }

        unset($visited[$objectId]);

        return $mappedProperties;
    }

    /**
     * @param array<int, true> $visited
     */
    private function resolvePropertyValue(PropertyReflector $property, object $object, array &$visited): mixed
    {
        if (! $property->isInitialized($object)) {
            return null;
        }

        $propertyValue = $property->getValue($object);

        if ($property->getIterableType()?->isClass()) {
            foreach ($propertyValue as $key => $value) {
                if (! is_object($value)) {
                    continue;
                }

                $propertyValue[$key] = $this->mapValue($value, $visited);
            }

            return $propertyValue;
        }

        if ($propertyValue !== null && ($serializer = $this->serializerFactory->in($this->context)->forProperty($property)) instanceof Serializer) {
            return $serializer->serialize($propertyValue);
        }

        if ($propertyValue !== null && is_object($propertyValue)) {
            return $this->mapValue($propertyValue, $visited);
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
