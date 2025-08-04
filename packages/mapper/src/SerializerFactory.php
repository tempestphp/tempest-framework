<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use TypeError;

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

    private function serializerMatches(Closure|string $for, TypeReflector|string $input): bool
    {
        if (is_callable($for)) {
            try {
                return $for($input);
            } catch (TypeError) { // @mago-expect best-practices/dont-catch-error
                return false;
            }
        }

        if ($for === $input) {
            return true;
        }

        if ($input instanceof TypeReflector) {
            return $input->getName() === $for || $input->matches($for);
        }

        return false;
    }

    private function resolveSerializer(Closure|string $serializerClass, PropertyReflector|TypeReflector|string $input): ?Serializer
    {
        if (is_string($serializerClass)) {
            return get($serializerClass);
        }

        try {
            return $serializerClass($input);
        } catch (TypeError) { // @mago-expect best-practices/dont-catch-error
            return null;
        }
    }

    public function forProperty(PropertyReflector $property): ?Serializer
    {
        $type = $property->getType();

        // Get SerializerWith from the property
        $serializeWith = $property->getAttribute(SerializeWith::class);

        // Get SerializerWith from the property's type if there's no property-defined SerializerWith
        if ($serializeWith === null && $type->isClass()) {
            $serializeWith = $type->asClass()->getAttribute(SerializeWith::class, recursive: true);

            if ($serializeWith === null && $type->asClass()->getAttribute(SerializeAs::class)) {
                $serializeWith = new SerializeWith(DtoSerializer::class);
            }
        }

        // Return the serializer if defined with SerializerWith
        if ($serializeWith !== null) {
            // Resolve the serializer from the container
            return get($serializeWith->className);
        }

        // Resolve serializer from manual additions
        foreach ($this->serializers as [$for, $serializerClass]) {
            if (! $this->serializerMatches($for, $type)) {
                continue;
            }

            $serializer = $this->resolveSerializer($serializerClass, $property);

            if ($serializer !== null) {
                return $serializer;
            }
        }

        return null;
    }

    public function forValue(mixed $value): ?Serializer
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value)) {
            $input = new ClassReflector($value)->getType();
        } else {
            $input = gettype($value);
        }

        // Resolve serializer from manual additions
        foreach ($this->serializers as [$for, $serializerClass]) {
            if (! $this->serializerMatches($for, $input)) {
                continue;
            }

            $serializer = $this->resolveSerializer($serializerClass, $input);

            if ($serializer !== null) {
                return $serializer;
            }
        }

        return null;
    }
}
