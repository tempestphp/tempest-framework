<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use TypeError;
use UnitEnum;

#[Singleton]
final class SerializerFactory
{
    /**
     * @var array<string,array{string|Closure,class-string<\Tempest\Mapper\Serializer>|Closure,int}[]>
     */
    private(set) array $serializers = [];

    private(set) Context|UnitEnum|string|null $context = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<\Tempest\Mapper\Serializer> $serializerClass
     */
    public function addSerializer(string|array|Closure $for, string|Closure $serializerClass, int $priority = 0, Context|UnitEnum|string|null $context = null): self
    {
        $context = MappingContext::from($context);

        $this->serializers[$context->key] ??= [];
        $this->serializers[$context->key][] = [$for, $serializerClass, $priority];

        usort($this->serializers[$context->key], static fn (array $a, array $b) => $a[2] <=> $b[2]);

        return $this;
    }

    /**
     * Sets the context that should be passed to serializers.
     */
    public function in(Context|UnitEnum|string $context): self
    {
        $serializer = clone $this;
        $serializer->context = $context;

        return $serializer;
    }

    public function forProperty(PropertyReflector $property): ?Serializer
    {
        $context = MappingContext::from($this->context);
        $type = $property->getType();
        $serializeWith = $property->getAttribute(SerializeWith::class);

        if ($serializeWith === null && $type->isClass()) {
            $serializeWith = $type->asClass()->getAttribute(SerializeWith::class, recursive: true);
        }

        if ($serializeWith !== null) {
            return $this->container->get($serializeWith->className, context: $context);
        }

        if ($serializerAttribute = $property->getAttribute(ProvidesSerializer::class)) {
            return $this->container->get($serializerAttribute->serializer, context: $context);
        }

        foreach ($this->resolveSerializers() as [$for, $serializerClass]) {
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

        foreach ($this->resolveSerializers() as [$for, $serializerClass]) {
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

    /**
     * @param Closure|class-string<Serializer|DynamicSerializer> $serializerClass
     */
    private function resolveSerializer(Closure|string $serializerClass, PropertyReflector|TypeReflector|string $input): ?Serializer
    {
        $context = MappingContext::from($this->context);

        if ($serializerClass instanceof Closure) {
            try {
                return $serializerClass($input, $context);
            } catch (TypeError) {
                return null;
            }
        }

        if (is_a($serializerClass, DynamicSerializer::class, allow_string: true)) {
            return $serializerClass::make($input, $context);
        }

        return $this->container->get($serializerClass, context: $context);
    }

    private function serializerMatches(Closure|string|array $for, TypeReflector|string $input): bool
    {
        if (is_array($for)) {
            return array_any($for, fn (Closure|string $forType) => $this->serializerMatches($forType, $input));
        }

        if (is_callable($for)) {
            try {
                return $for($input);
            } catch (TypeError) {
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

    /**
     * @return array{string|Closure,class-string<\Tempest\Mapper\Serializer>|Closure,int}[]
     */
    private function resolveSerializers(): array
    {
        return [
            ...($this->serializers[MappingContext::from($this->context)->key] ?? []),
            ...($this->serializers[MappingContext::default()->key] ?? []),
        ];
    }
}
