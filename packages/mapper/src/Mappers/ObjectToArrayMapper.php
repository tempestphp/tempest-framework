<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use JsonSerializable;
use Tempest\Mapper\Context;
use Tempest\Mapper\Hidden;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;

use function Tempest\Mapper\map;

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
        if ($from instanceof JsonSerializable) {
            return $from->jsonSerialize();
        }

        if (is_object($from)) {
            $class = new ClassReflector($from);

            $mappedProperties = [];

            foreach ($class->getPublicProperties() as $property) {
                if ($property->hasAttribute(Hidden::class)) {
                    continue;
                }

                $propertyName = $this->resolvePropertyName($property);
                $propertyValue = $this->resolvePropertyValue($property, $from);
                $mappedProperties[$propertyName] = $propertyValue;
            }
        } else {
            $mappedProperties = $from;
        }

        return $mappedProperties;
    }

    private function resolvePropertyValue(PropertyReflector $property, object $object): mixed
    {
        if (! $property->isInitialized($object)) {
            return null;
        }

        $propertyValue = $property->getValue($object);

        if ($property->getIterableType()?->isClass()) {
            foreach ($propertyValue as $key => $value) {
                if (is_object($value)) {
                    $propertyValue[$key] = map($value)->toArray();
                }
            }

            return $propertyValue;
        }

        if ($propertyValue !== null && ($serializer = $this->serializerFactory->in($this->context)->forProperty($property)) !== null) {
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
