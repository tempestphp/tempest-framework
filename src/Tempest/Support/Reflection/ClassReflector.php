<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Generator;
use ReflectionClass as PHPReflectionClass;
use ReflectionProperty;
use ReflectionProperty as PHPReflectionProperty;

final readonly class ClassReflector implements Reflector
{
    use HasAttributes;

    private PHPReflectionClass $reflectionClass;

    public function __construct(string|object $reflectionClass)
    {
        if (is_string($reflectionClass)) {
            $reflectionClass = new PHPReflectionClass($reflectionClass);
        } elseif (! $reflectionClass instanceof PHPReflectionClass && is_object($reflectionClass)) {
            $reflectionClass = new PHPReflectionClass($reflectionClass);
        }

        $this->reflectionClass = $reflectionClass;
    }

    public function getReflection(): PHPReflectionClass
    {
        return $this->reflectionClass;
    }

    /** @return Generator|PropertyReflector[] */
    public function getPublicProperties(): Generator
    {
        foreach ($this->reflectionClass->getProperties(PHPReflectionProperty::IS_PUBLIC) as $property) {
            yield new PropertyReflector($property);
        }
    }

    public function getProperty(string $name): PropertyReflector
    {
        return new PropertyReflector(new ReflectionProperty($this->reflectionClass->getName(), $name));
    }

    public function getName(): string
    {
        return $this->reflectionClass->getName();
    }

    public function getShortName(): string
    {
        return $this->reflectionClass->getShortName();
    }

    public function getType(): TypeReflector
    {
        return new TypeReflector($this->reflectionClass);
    }

    public function getConstructor(): ?MethodReflector
    {
        $constructor = $this->reflectionClass->getConstructor();

        if ($constructor === null) {
            return null;
        }

        return new MethodReflector($constructor);
    }

    public function getMethod(string $name): ?MethodReflector
    {
        return new MethodReflector($this->reflectionClass->getMethod($name));
    }

    public function isInstantiable(): bool
    {
        return $this->reflectionClass->isInstantiable();
    }

    public function newInstanceWithoutConstructor(): object
    {
        return $this->reflectionClass->newInstanceWithoutConstructor();
    }

    public function newInstanceArgs(array $args = []): object
    {
        return $this->reflectionClass->newInstanceArgs($args);
    }

    public function callStatic(string $method, mixed ...$args): mixed
    {
        $className = $this->getName();

        return $className::$method(...$args);
    }
}
