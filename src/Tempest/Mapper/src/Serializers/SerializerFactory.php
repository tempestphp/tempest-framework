<?php

namespace Tempest\Mapper\Serializers;

use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Tempest\Mapper\Serializer;
use Tempest\Mapper\SerializeWith;
use Tempest\Reflection\PropertyReflector;

use function Tempest\get;

final class SerializerFactory
{
    public function forProperty(PropertyReflector $property): ?Serializer
    {
        $type = $property->getType();

        // Get SerializerWith from the property
        $serializeWith = $property->getAttribute(SerializeWith::class);

        // Get SerializerWith from the property's type if there's no property-defined SerializerWith
        if ($serializeWith === null && $type->isClass()) {
            $serializeWith = $type->asClass()->getAttribute(SerializeWith::class, recursive: true);
        }

        // Return the serializer if defined with SerializerWith
        if ($serializeWith !== null) {
            // Resolve the serializer from the container
            return get($serializeWith->className);
        }

        // If the type is an enum, we'll use the enum serializer
        if ($type->matches(BackedEnum::class)) {
            return new EnumSerializer();
        }

        // If the property has an iterable type, we'll cast it with the array object caster
        if ($property->getIterableType() !== null) {
            return new ObjectToArraySerializer();
        }

        // Try a built-in caster
        $builtInCaster = match ($type->getName()) {
            DateTimeImmutable::class, DateTimeInterface::class, DateTime::class => DateTimeSerializer::fromProperty($property),
            'array' => new ArrayToJsonSerializer(),
            default => null,
        };

        if ($builtInCaster !== null) {
            return $builtInCaster;
        }

        return null;
    }
}
