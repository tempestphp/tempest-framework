<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Exception;
use ReflectionClass as PHPReflectionClass;
use ReflectionIntersectionType as PHPReflectionIntersectionType;
use ReflectionNamedType as PHPReflectionNamedType;
use ReflectionParameter as PHPReflectionParameter;
use ReflectionProperty as PHPReflectionProperty;
use ReflectionType as PHPReflectionType;
use ReflectionUnionType as PHPReflectionUnionType;
use Reflector as PHPReflector;

final readonly class TypeReflector implements Reflector
{
    private string $definition;

    private TypeValidator $validator;

    public function __construct(
        private PHPReflector|PHPReflectionType|string $reflector,
    ) {
        $this->definition = $this->resolveDefinition($this->reflector);
        $this->validator = new TypeValidator();
    }

    public function asClass(): ClassReflector
    {
        return new ClassReflector($this->definition);
    }

    public function equals(string|TypeReflector $type): bool
    {
        if (is_string($type)) {
            $type = new TypeReflector($type);
        }

        return $this->definition === $type->definition;
    }

    public function accepts(mixed $input): bool
    {
        return $this->validator->accepts($this->definition, $input);
    }

    public function matches(string $className): bool
    {
        return $this->validator->matches($this->definition, $className);
    }

    public function getName(): string
    {
        return $this->definition;
    }

    public function getShortName(): string
    {
        $parts = explode('\\', $this->definition);

        return $parts[array_key_last($parts)];
    }

    public function isBuiltIn(): bool
    {
        return $this->validator->isBuiltIn($this->definition);
    }

    public function isClass(): bool
    {
        return $this->validator->isClass($this->definition);
    }

    public function isIterable(): bool
    {
        return $this->validator->isIterable($this->definition);
    }

    public function isNullable(): bool
    {
        return $this->validator->isNullable($this->definition);
    }

    public function split(): array
    {
        return array_map(
            fn (string $part) => new self($part),
            $this->validator->split($this->definition),
        );
    }

    private function resolveDefinition(PHPReflector|PHPReflectionType|string $reflector): string
    {
        if (is_string($reflector)) {
            return $reflector;
        }

        if (
            $reflector instanceof PHPReflectionParameter
            || $reflector instanceof PHPReflectionProperty
        ) {
            return $this->resolveDefinition($reflector->getType());
        }

        if ($reflector instanceof PHPReflectionClass) {
            return $reflector->getName();
        }

        if ($reflector instanceof PHPReflectionNamedType) {
            return $reflector->getName();
        }

        if ($reflector instanceof PHPReflectionUnionType) {
            return implode('|', array_map(
                fn (PHPReflectionType $reflectionType) => $this->resolveDefinition($reflectionType),
                $reflector->getTypes(),
            ));
        }

        if ($reflector instanceof PHPReflectionIntersectionType) {
            return implode('&', array_map(
                fn (PHPReflectionType $reflectionType) => $this->resolveDefinition($reflectionType),
                $reflector->getTypes(),
            ));
        }

        throw new Exception('Could not resolve type');
    }
}
