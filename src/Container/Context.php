<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

final class Context
{
    public function __construct(
        public ReflectionClass|ReflectionMethod|ReflectionFunction $reflector,
        /** @var \Tempest\Container\Dependency[] $dependencies */
        public array $dependencies = [],
    ) {
    }

    public function addDependency(Dependency $dependency): self
    {
        $this->dependencies[] = $dependency;

        return $this;
    }

    public function currentDependency(): ?Dependency
    {
        return $this->dependencies[array_key_last($this->dependencies)] ?? null;
    }

    public function getId(): string
    {
        return match($this->reflector::class) {
            ReflectionClass::class, ReflectionFunction::class => $this->reflector->getName(),
            ReflectionMethod::class => $this->reflector->getDeclaringClass()->getName(),
        };
    }

    public function __toString(): string
    {
        return match($this->reflector::class) {
            ReflectionClass::class => $this->classToString($this->reflector),
            ReflectionMethod::class => $this->methodToString($this->reflector),
            ReflectionFunction::class => $this->functionToString($this->reflector),
        };
    }

    private function classToString(ReflectionClass $reflector): string
    {
        return $reflector->getShortName();
    }

    private function methodToString(ReflectionMethod $reflector): string
    {
        return $reflector->getDeclaringClass()->getShortName() . '::' . $reflector->getName() . '(' . $this->dependenciesToString() . ')';
    }

    private function functionToString(ReflectionFunction $reflector): string
    {
        return $reflector->getShortName() . '(' . $this->dependenciesToString() . ')';
    }

    private function dependenciesToString(): string
    {
        return implode(
            ', ',
            array_map(
                fn (Dependency $dependency) => (string) $dependency,
                $this->dependencies,
            )
        );
    }
}
