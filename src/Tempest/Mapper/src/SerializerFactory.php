<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionException;
use Tempest\Mapper\Serializers\ArraySerializer;
use Tempest\Mapper\Serializers\DateTimeSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tempest\Reflection\PropertyReflector;
use UnitEnum;
use function Tempest\get;

final readonly class SerializerFactory
{
    public function forProperty(PropertyReflector $property): ?Serializer
    {
        $type = $property->getType();

        // Get SerializeWith from the property
        $serializeWith = $property->getAttribute(SerializeWith::class);

        // Get SerializeWith from the property's type if there's no property-defined SerializeWith
        if ($serializeWith === null) {
            try {
                $serializeWith = $type->asClass()->getAttribute(SerializeWith::class, recursive: true);
            } catch (ReflectionException) {
                // Could not resolve SerializeWith from the type
            }
        }

        // Return the serializer if defined with SerializeWith
        if ($serializeWith !== null) {
            // Resolve the serializer from the container
            return get($serializeWith->className);
        }

        // Check if backed enum
        if ($type->matches(UnitEnum::class)) {
            return new EnumSerializer();
        }

        // Try a built-in serializer
        return match ($type->getName()) {
            'array' => new ArraySerializer(),
            DateTimeImmutable::class, DateTimeInterface::class, DateTime::class => DateTimeSerializer::fromProperty($property),
            default => null,
        };
    }
}
