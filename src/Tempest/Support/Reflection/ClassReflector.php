<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Generator;
use ReflectionClass as PHPReflectionClass;
use ReflectionProperty as PHPReflectionProperty;

final readonly class ClassReflector implements Reflector
{
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

    /** @return Generator|PropertyReflector[] */
    public function getPublicProperties(): Generator
    {
        foreach ($this->reflectionClass->getProperties(PHPReflectionProperty::IS_PUBLIC) as $property) {
            yield new PropertyReflector($property);
        }
    }

    public function getName(): string
    {
        return $this->reflectionClass->getName();
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
}
