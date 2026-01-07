<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Memoization\HasMemoization;
use UnitEnum;

#[Singleton]
final class SerializerFactory
{
    use HasMemoization;

    /**
     * @var array<string,array{class-string<\Tempest\Mapper\Serializer>,int}[]>
     */
    private(set) array $serializers = [];

    private(set) Context|UnitEnum|string|null $context = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<\Tempest\Mapper\Serializer> $serializerClass
     */
    public function addSerializer(string $serializerClass, int $priority = 0, Context|UnitEnum|string|null $context = null): self
    {
        $context = MappingContext::from($context);

        $this->serializers[$context->name] ??= [];
        $this->serializers[$context->name][] = [$serializerClass, $priority];

        usort($this->serializers[$context->name], static fn (array $a, array $b) => $a[1] <=> $b[1]);

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

        return $this->memoize('[' . $context->name . '] ' . $property->getName(), function () use ($property, $context) {
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

            foreach ($this->resolveSerializers() as [$serializerClass]) {
                if (is_a($serializerClass, DynamicSerializer::class, allow_string: true)) {
                    if (! $serializerClass::accepts($property)) {
                        continue;
                    }
                }

                $serializer = $this->resolveSerializer($serializerClass, $property);

                if ($serializer !== null) {
                    return $serializer;
                }
            }

            return null;
        });
    }

    public function forValue(mixed $value): ?Serializer
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value)) {
            $input = new ClassReflector($value)->getType();
        } else {
            $input = new TypeReflector(gettype($value));
        }

        foreach ($this->resolveSerializers() as [$serializerClass]) {
            if (is_a($serializerClass, DynamicSerializer::class, allow_string: true)) {
                if (! $serializerClass::accepts($input)) {
                    continue;
                }
            }

            $serializer = $this->resolveSerializer($serializerClass, $input);

            if ($serializer !== null) {
                return $serializer;
            }
        }

        return null;
    }

    /**
     * @param Closure|class-string<Serializer|ConfigurableSerializer> $serializerClass
     */
    private function resolveSerializer(string $serializerClass, PropertyReflector|TypeReflector|string $input): ?Serializer
    {
        $context = MappingContext::from($this->context);

        if (is_a($serializerClass, ConfigurableSerializer::class, allow_string: true)) {
            return $serializerClass::configure($input, $context);
        }

        return $this->container->get($serializerClass, context: $context);
    }

    /**
     * @return array{class-string<Serializer|ConfigurableSerializer>|Closure,int}[]
     */
    private function resolveSerializers(): array
    {
        return [
            ...($this->serializers[MappingContext::from($this->context)->name] ?? []),
            ...($this->serializers[MappingContext::default()->name] ?? []),
        ];
    }
}
