<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Mapper\Serializers\DataTransferObjectSerializer;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use TypeError;

#[Singleton]
final class SerializerFactory
{
    /**
     * @var array<string,array{string|Closure,class-string<\Tempest\Mapper\Serializer>|Closure,int}[]>
     */
    private(set) array $contextSerializers = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<\Tempest\Mapper\Serializer> $serializerClass
     */
    public function addSerializer(string|array|Closure $for, string|Closure $serializerClass, string $context = Context::DEFAULT, int $priority = 0): self
    {
        if (! isset($this->contextSerializers[$context])) {
            $this->contextSerializers[$context] = [];
        }

        $this->contextSerializers[$context][] = [$for, $serializerClass, $priority];

        usort($this->contextSerializers[$context], static fn (array $a, array $b) => $a[2] <=> $b[2]);

        return $this;
    }

    public function forProperty(PropertyReflector $property, string $context = Context::DEFAULT): ?Serializer
    {
        $type = $property->getType();
        $serializeWith = $property->getAttribute(SerializeWith::class);

        if ($serializeWith === null && $type->isClass()) {
            $serializeWith = $type->asClass()->getAttribute(SerializeWith::class, recursive: true);

            if ($serializeWith === null && $type->asClass()->getAttribute(SerializeAs::class)) {
                $serializeWith = new SerializeWith(DataTransferObjectSerializer::class);
            }
        }

        if ($serializeWith !== null) {
            return $this->container->get($serializeWith->className);
        }

        if ($serializerAttribute = $property->getAttribute(ProvidesSerializer::class)) {
            return $this->container->get($serializerAttribute->serializer);
        }

        $serializers = [
            ...($this->contextSerializers[$context] ?? []),
            ...($this->contextSerializers[Context::DEFAULT] ?? []),
        ];

        foreach ($serializers as [$for, $serializerClass]) {
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

    public function forValue(mixed $value, string $context = Context::DEFAULT): ?Serializer
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value)) {
            $input = new ClassReflector($value)->getType();
        } else {
            $input = gettype($value);
        }

        $serializers = [
            ...($this->contextSerializers[$context] ?? []),
            ...($this->contextSerializers[Context::DEFAULT] ?? []),
        ];

        foreach ($serializers as [$for, $serializerClass]) {
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

    private function resolveSerializer(Closure|string $serializerClass, PropertyReflector|TypeReflector|string $input): ?Serializer
    {
        if ($serializerClass instanceof Closure) {
            try {
                return $serializerClass($input);
            } catch (TypeError) {
                return null;
            }
        }

        if (is_a($serializerClass, DynamicSerializer::class, allow_string: true)) {
            return $serializerClass::make($input);
        }

        return $this->container->get($serializerClass);
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
}
