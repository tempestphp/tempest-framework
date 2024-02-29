<?php

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
    ) {}

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
            ReflectionClass::class => $this->reflector->getShortName(),
            ReflectionMethod::class => $this->reflector->getDeclaringClass()->getShortName() . '::' . $this->reflector->getShortName() . '()',
            ReflectionFunction::class => $this->reflector->getShortName() . '()',
        };
    }

    private function classToString(ReflectionClass $reflector): string
    {
        return $reflector->getShortName();
    }

    private function methodToString(ReflectionMethod $reflector): string
    {
        return $reflector->getDeclaringClass()->getShortName() . '::' . $reflector->getName() . '()';
    }

    private function functionToString(ReflectionFunction $reflector): string
    {
        return $reflector->getShortName() . '()';
    }
}