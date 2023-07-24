<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/* @template ClassType of object */
class Attributes
{
    /** @var class-string<ClassType>|null */
    private ?string $instanceOf = null;

    public function __construct(
        private readonly ReflectionClass|ReflectionMethod|ReflectionProperty $reflector,
    ) {
    }

    public static function forClass(ReflectionClass|string $class): self
    {
        $class = $class instanceof ReflectionClass
            ? $class
            : new ReflectionClass($class);

        return new self($class);
    }

    public static function forMethod(
        ReflectionMethod|ReflectionClass|string $classOrMethod,
        ?string $methodName = null
    ): self {
        if ($classOrMethod instanceof ReflectionMethod) {
            return new self($classOrMethod);
        }

        if ($classOrMethod instanceof ReflectionClass) {
            $classOrMethod = $classOrMethod->getName();
        }

        return new self(new ReflectionMethod($classOrMethod, $methodName));
    }

    public static function forProperty(ReflectionProperty $property): self
    {
        return new self($property);
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return self<T>
     */
    public function instanceOf(string $attributeClass): self
    {
        $this->instanceOf = $attributeClass;

        return $this;
    }

    /**
     * @return ClassType[]
     */
    public function all(): array
    {
        return array_map(
            fn (ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance(),
            $this->resolveAttributes($this->reflector),
        );
    }

    /**
     * @return ClassType
     */
    public function first(): ?object
    {
        $firstAttribute = $this->resolveAttributes($this->reflector)[0] ?? null;

        return $firstAttribute?->newInstance();
    }

    private function resolveAttributes(ReflectionClass|ReflectionMethod|ReflectionProperty $reflector): array
    {
        return $reflector->getAttributes(
            name: $this->instanceOf,
            flags: ReflectionAttribute::IS_INSTANCEOF,
        );
    }
}
