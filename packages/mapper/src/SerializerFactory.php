<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Reflection\PropertyReflector;

use function Tempest\get;

final class SerializerFactory
{
    /**
     * @var array{string|Closure, class-string<\Tempest\Mapper\Serializer>|Closure}[]
     */
    private array $serializers = [];

    /**
     * @param class-string<\Tempest\Mapper\Serializer> $serializerClass
     */
    public function addSerializer(string|Closure $for, string|Closure $serializerClass): self
    {
        $this->serializers = [[$for, $serializerClass], ...$this->serializers];

        return $this;
    }

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

        // Resolve serializer from manual additions
        foreach ($this->serializers as [$for, $serializerClass]) {
            if (is_callable($for) && $for($property) || is_string($for) && $type->matches($for) || $type->getName() === $for) {
                return is_callable($serializerClass)
                    ? $serializerClass($property)
                    : get($serializerClass);
            }
        }

        return null;
    }
}
