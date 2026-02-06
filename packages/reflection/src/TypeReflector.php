<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use BackedEnum;
use DateTimeInterface;
use Generator;
use Iterator;
use ReflectionClass as PHPReflectionClass;
use ReflectionIntersectionType as PHPReflectionIntersectionType;
use ReflectionNamedType as PHPReflectionNamedType;
use ReflectionParameter as PHPReflectionParameter;
use ReflectionProperty as PHPReflectionProperty;
use ReflectionType as PHPReflectionType;
use ReflectionUnionType as PHPReflectionUnionType;
use Reflector as PHPReflector;
use Stringable;
use UnitEnum;

final readonly class TypeReflector implements Reflector
{
    private const array BUILTIN_VALIDATION = [
        'array' => 'is_array',
        'bool' => 'is_bool',
        'callable' => 'is_callable',
        'float' => 'is_float',
        'int' => 'is_int',
        'null' => 'is_null',
        'object' => 'is_object',
        'resource' => 'is_resource',
        'string' => 'is_string',
        // these are handled explicitly
        'false' => null,
        'mixed' => null,
        'never' => null,
        'true' => null,
        'void' => null,
    ];

    private const array SCALAR_TYPES = [
        'bool',
        'string',
        'int',
        'float',
    ];

    private string $definition;

    private string $cleanDefinition;

    private bool $isNullable;

    public function __construct(
        private PHPReflector|PHPReflectionType|string $reflector,
    ) {
        $this->definition = $this->resolveDefinition($this->reflector);
        $this->isNullable = $this->resolveIsNullable($this->reflector);
        $this->cleanDefinition = str_replace('?', '', $this->definition);
    }

    public function asClass(): ClassReflector
    {
        return new ClassReflector($this->cleanDefinition);
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
        if ($this->isNullable && $input === null) {
            return true;
        }

        if ($this->isBuiltIn()) {
            return match ($this->cleanDefinition) {
                'false' => $input === false,
                'mixed' => true,
                'never' => false,
                'true' => $input === true,
                'void' => false,
                default => self::BUILTIN_VALIDATION[$this->cleanDefinition]($input),
            };
        }

        if ($this->isClass()) {
            if (is_string($input)) {
                return $this->matches($input);
            }

            $cleanDefinition = $this->cleanDefinition;

            return $input instanceof $cleanDefinition;
        }

        if ($this->isInterface()) {
            return $input instanceof $this->definition;
        }

        if ($this->isIterable()) {
            return is_iterable($input);
        }

        if (str_contains($this->definition, '|')) {
            return array_any($this->split(), static fn ($type) => $type->accepts($input));
        }

        if (str_contains($this->definition, '&')) {
            return array_all($this->split(), static fn ($type) => $type->accepts($input));
        }

        return false;
    }

    public function matches(string $className): bool
    {
        return is_a($this->cleanDefinition, $className, allow_string: true);
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
        return isset(self::BUILTIN_VALIDATION[$this->cleanDefinition]);
    }

    public function isScalar(): bool
    {
        return in_array($this->cleanDefinition, self::SCALAR_TYPES, strict: true);
    }

    public function isClass(): bool
    {
        return class_exists($this->cleanDefinition);
    }

    public function isEnum(): bool
    {
        return $this->isUnitEnum() || $this->isBackedEnum();
    }

    public function isUnitEnum(): bool
    {
        return $this->matches(UnitEnum::class);
    }

    public function isBackedEnum(): bool
    {
        return $this->matches(BackedEnum::class);
    }

    // TODO: should be refactored outside of the reflector component
    public function isRelation(): bool
    {
        return $this->isClass() && ! $this->isEnum() && ! $this->isIterable() && ! $this->isStringable() && ! $this->matches(DateTimeInterface::class);
    }

    public function isInterface(): bool
    {
        return interface_exists($this->cleanDefinition);
    }

    public function isIterable(): bool
    {
        if ($this->matches(Iterator::class)) {
            return true;
        }

        return in_array($this->cleanDefinition, ['array', 'iterable', Generator::class], strict: true);
    }

    public function isStringable(): bool
    {
        if ($this->matches(Stringable::class)) {
            return true;
        }

        return in_array($this->cleanDefinition, ['string'], strict: true);
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function isUnion(): bool
    {
        return str_contains($this->definition, '|');
    }

    public function isIntersection(): bool
    {
        return str_contains($this->definition, '&');
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

        if ($reflector instanceof PHPReflectionParameter || $reflector instanceof PHPReflectionProperty) {
            $type = $reflector->getType();

            if ($type === null) {
                return 'mixed';
            }

            return $this->resolveDefinition($type);
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

        throw new \InvalidArgumentException(
            sprintf('Could not resolve type for reflector of type: %s', get_debug_type($reflector)),
        );
    }

    private function resolveIsNullable(PHPReflectionType|PHPReflector|string $reflector): bool
    {
        if (is_string($reflector)) {
            if (str_contains($this->definition, '?')) {
                return true;
            }

            return array_any(
                array: preg_split('/[&|]/', $this->definition),
                callback: static fn (string $type) => $type === 'null',
            );
        }

        if ($reflector instanceof PHPReflectionParameter || $reflector instanceof PHPReflectionProperty) {
            $type = $reflector->getType();

            return $type === null || $type->allowsNull();
        }

        if ($reflector instanceof PHPReflectionType) {
            return $reflector->allowsNull();
        }

        return false;
    }
}
