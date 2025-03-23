<?php

declare(strict_types=1);

namespace Tempest\Container;

use Closure;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Reflection\Reflector;
use Tempest\Reflection\TypeReflector;

final readonly class Dependency
{
    public function __construct(
        public Reflector|Closure|string $dependency,
    ) {}

    public function getName(): string
    {
        return $this->resolveName($this->dependency);
    }

    public function getShortName(): string
    {
        return $this->resolveShortName($this->dependency);
    }

    public function equals(self $other): bool
    {
        return $this->getName() === $other->getName();
    }

    public function getTypeName(): string
    {
        $dependency = $this->dependency;

        if (is_string($dependency)) {
            $parts = explode('\\', $dependency);

            return $parts[array_key_last($parts)];
        }

        return match ($dependency::class) {
            ClassReflector::class => $dependency->getType()->getShortName(),
            MethodReflector::class => $dependency->getDeclaringClass()->getType()->getShortName(),
            ParameterReflector::class => $dependency->getType()->getShortName(),
            TypeReflector::class => $dependency->getShortName(),
            default => 'unknown',
        };
    }

    private function resolveName(Reflector|Closure|string $dependency): string
    {
        if (is_string($dependency)) {
            return $dependency;
        }

        return match ($dependency::class) {
            FunctionReflector::class => $dependency->getName() . ' in ' . $dependency->getFileName() . ':' . $dependency->getStartLine(),
            ClassReflector::class => $dependency->getName(),
            MethodReflector::class => $dependency->getDeclaringClass()->getName() . '::' . $dependency->getName(),
            ParameterReflector::class => $dependency->getType()->getName(),
            TypeReflector::class => $dependency->getName(),
            default => 'unknown',
        };
    }

    private function resolveShortName(Reflector|Closure|string $dependency): string
    {
        if (is_string($dependency)) {
            return $dependency;
        }

        return match ($dependency::class) {
            FunctionReflector::class => $dependency->getShortName() . ' in ' . $dependency->getFileName() . ':' . $dependency->getStartLine(),
            ClassReflector::class => $dependency->getShortName(),
            MethodReflector::class => $dependency->getShortName(),
            ParameterReflector::class => $dependency->getType()->getShortName(),
            TypeReflector::class => $dependency->getShortName(),
            default => 'unknown',
        };
    }
}
