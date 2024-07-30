<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Exception;
use Generator;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType as PHPReflectionType;
use ReflectionUnionType;
use Reflector as PHPReflector;
use TypeError;

final readonly class TypeReflector implements Reflector
{
    private string $definition;

    public function __construct(
        private PHPReflector|PHPReflectionType|string $reflector,
    ) {
        $this->definition = $this->resolveDefinition($this->reflector);
    }

    public function asClass(): ClassReflector
    {
        return new ClassReflector($this->definition);
    }

    public function accepts(mixed $input): bool
    {
        $test = eval(sprintf('return fn (%s $input) => $input;', $this->definition));

        try {
            $test($input);
        } catch (TypeError) {
            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return $this->definition;
    }

    public function isBuiltIn(): bool
    {
        return in_array($this->definition, [
            'string',
            'bool',
            'float',
            'int',
            'array',
            'null',
            'object',
            'callable',
            'resource',
            'never',
            'void',
            'true',
            'false',
        ]);
    }

    public function isIterable(): bool
    {
        return in_array($this->definition, [
            'array',
            'iterable',
            Generator::class,
        ]);
    }

    /** @return self[] */
    public function split(): array
    {
        return array_map(
            fn (string $part) => new self($part),
            preg_split('/[&|]/', $this->definition),
        );
    }

    private function resolveDefinition(PHPReflector|PHPReflectionType|string $reflector): string
    {
        if (is_string($reflector)) {
            return $reflector;
        }

        if (
            $reflector instanceof ReflectionParameter
            || $reflector instanceof ReflectionProperty
        ) {
            return $this->resolveDefinition($reflector->getType());
        }

        if ($reflector instanceof ReflectionClass) {
            return $reflector->getName();
        }

        if ($reflector instanceof ReflectionNamedType) {
            return $reflector->getName();
        }

        if ($reflector instanceof ReflectionUnionType) {
            return implode('|', array_map(
                fn (PHPReflectionType $reflectionType) => $this->resolveDefinition($reflectionType),
                $reflector->getTypes(),
            ));
        }

        if ($reflector instanceof ReflectionIntersectionType) {
            return implode('&', array_map(
                fn (PHPReflectionType $reflectionType) => $this->resolveDefinition($reflectionType),
                $reflector->getTypes(),
            ));
        }

        throw new Exception('Could not resolve type');
    }
}
