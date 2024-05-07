<?php

declare(strict_types=1);

namespace Tempest\Container;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Reflector;

final readonly class Dependency
{
    public function __construct(
        public Reflector|ReflectionType|Closure|string $dependency,
    ) {
    }

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

        return match($dependency::class) {
            ReflectionClass::class => $dependency->getShortName(),
            ReflectionMethod::class => $dependency->getDeclaringClass()->getShortName(),
            ReflectionParameter::class => $this->resolveName($dependency->getType()),
            ReflectionNamedType::class => $dependency->getName(),
            ReflectionIntersectionType::class => $this->intersectionTypeToString($dependency),
            ReflectionUnionType::class => $this->unionTypeToString($dependency),
            default => 'unknown',
        };
    }

    private function resolveName(ReflectionType|Reflector|string|Closure $dependency): string
    {
        if (is_string($dependency)) {
            return $dependency;
        }

        return match($dependency::class) {
            ReflectionFunction::class => $dependency->getName() . ' in ' . $dependency->getFileName() . ':' . $dependency->getStartLine(),
            ReflectionClass::class => $dependency->getName(),
            ReflectionMethod::class => $dependency->getDeclaringClass()->getName() . '::' . $dependency->getName(),
            ReflectionParameter::class => $this->resolveName($dependency->getType()),
            ReflectionNamedType::class => $dependency->getName(),
            ReflectionIntersectionType::class => $this->intersectionTypeToString($dependency),
            ReflectionUnionType::class => $this->unionTypeToString($dependency),
            default => 'unknown',
        };
    }

    private function resolveShortName(ReflectionType|Reflector|string|Closure $dependency): string
    {
        if (is_string($dependency)) {
            return $dependency;
        }

        return match($dependency::class) {
            ReflectionFunction::class => $dependency->getShortName() . ' in ' . $dependency->getFileName() . ':' . $dependency->getStartLine(),
            ReflectionClass::class => $dependency->getShortName(),
            ReflectionMethod::class => $this->reflectionMethodToShortString($dependency),
            ReflectionParameter::class => $this->resolveShortName($dependency->getType()),
            ReflectionNamedType::class => $this->reflectionNameTypeToShortString($dependency),
            ReflectionIntersectionType::class => $this->intersectionTypeToString($dependency),
            ReflectionUnionType::class => $this->unionTypeToString($dependency),
            default => 'unknown',
        };
    }

    private function intersectionTypeToString(ReflectionIntersectionType $type): string
    {
        return implode(
            '&',
            array_map(
                fn (ReflectionType $subType) => $this->resolveName($subType),
                $type->getTypes(),
            ),
        );
    }

    private function unionTypeToString(ReflectionUnionType $type): string
    {
        return implode(
            '|',
            array_map(
                fn (ReflectionType $subType) => $this->resolveName($subType),
                $type->getTypes(),
            ),
        );
    }

    private function reflectionMethodToShortString(ReflectionMethod $method): string
    {
        $string = $method->getDeclaringClass()->getShortName() . '::' . $method->getName() . '(';

        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $this->resolveShortName($parameter) . ' $' . $parameter->getName();
        }

        $string .= implode(', ', $parameters);

        $string .= ')';

        return $string;
    }

    private function reflectionNameTypeToShortString(ReflectionNamedType $type): string
    {
        $parts = explode('\\', $type->getName());

        return $parts[array_key_last($parts)] ?? $type->getName();
    }
}
