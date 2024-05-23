<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/** @template T */
final class Attributes
{
    private ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionParameter $reflector;

    public function __construct(
        private readonly string $attributeName,
    ) {
    }

    /**
     * @template U
     * @param class-string<U> $attributeName
     * @return self<U>
     */
    public static function find(string $attributeName): self
    {
        return new self($attributeName);
    }

    /**
     * @param ReflectionClass|ReflectionMethod|ReflectionProperty $reflector
     * @return $this<T>
     */
    public function in(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionParameter|string $reflector): self
    {
        if (is_string($reflector)) {
            $reflector = new ReflectionClass($reflector);
        }

        $this->reflector = $reflector;

        return $this;
    }

    /**
     * @return T[]
     */
    public function all(): array
    {
        return array_map(
            fn (ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance(),
            $this->resolveAttributes(),
        );
    }

    /**
     * @return T|null
     */
    public function first(): object|null
    {
        $firstAttribute = $this->resolveAttributes()[0] ?? null;

        return $firstAttribute?->newInstance();
    }

    /**
     * @return ReflectionAttribute[]
     */
    private function resolveAttributes(): array
    {
        return $this->reflector->getAttributes(
            name: $this->attributeName,
            flags: ReflectionAttribute::IS_INSTANCEOF,
        );
    }
}
