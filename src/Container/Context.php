<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

final class Context
{
    public function __construct(
        private ReflectionClass|ReflectionMethod|ReflectionFunction $reflector,
        /** @var \Tempest\Container\Dependency[] $dependencies */
        private array $dependencies = [],
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

    public function getName(): string
    {
        return match($this->reflector::class) {
            ReflectionClass::class => $this->reflector->getName(),
            ReflectionMethod::class => $this->reflector->getDeclaringClass()->getName(),
            ReflectionFunction::class => $this->reflector->getName() . ' in ' . $this->reflector->getFileName() . ':' . $this->reflector->getStartLine(),
        };
    }

    public function getShortName(): string
    {
        return match($this->reflector::class) {
            ReflectionClass::class => $this->reflector->getShortName(),
            ReflectionMethod::class => $this->reflector->getDeclaringClass()->getShortName(),
            ReflectionFunction::class => $this->reflector->getShortName() . ' in ' . $this->reflector->getFileName() . ':' . $this->reflector->getStartLine(),
        };
    }

    public function __toString(): string
    {
        return match($this->reflector::class) {
            ReflectionClass::class => $this->classToString(),
            ReflectionMethod::class => $this->methodToString(),
            ReflectionFunction::class => $this->functionToString(),
        };
    }

    private function classToString(): string
    {
        return $this->reflector->getShortName();
    }

    private function methodToString(): string
    {
        return $this->reflector->getDeclaringClass()->getShortName() . '::' . $this->reflector->getName() . '(' . $this->dependenciesToString() . ')';
    }

    private function functionToString(): string
    {
        return $this->reflector->getShortName() . ' in ' . $this->reflector->getFileName() . ':' . $this->reflector->getStartLine() . '(' . $this->dependenciesToString() . ')';
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
