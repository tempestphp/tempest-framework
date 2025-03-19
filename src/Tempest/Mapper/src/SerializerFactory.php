<?php

namespace Tempest\Mapper;

use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Serializable;
use Stringable;
use Tempest\Mapper\Serializers\ArrayToJsonSerializer;
use Tempest\Mapper\Serializers\BooleanSerializer;
use Tempest\Mapper\Serializers\DateTimeSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tempest\Mapper\Serializers\FloatSerializer;
use Tempest\Mapper\Serializers\IntegerSerializer;
use Tempest\Mapper\Serializers\ObjectToArraySerializer;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tempest\Mapper\Serializers\StringSerializer;
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

        if ($type->matches(Serializable::class) || $type->matches(JsonSerializable::class)) {
            return new SerializableSerializer();
        }

        if ($type->getName() === 'bool') {
            return new BooleanSerializer();
        }

        if ($type->getName() === 'float') {
            return new FloatSerializer();
        }

        if ($type->getName() === 'int') {
            return new IntegerSerializer();
        }

        if ($type->getName() === 'string' || $type->matches(Stringable::class)) {
            return new StringSerializer();
        }

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
