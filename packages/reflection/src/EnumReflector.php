<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Closure;
use ReflectionEnum as PHPReflectionEnum;
use ReflectionEnumUnitCase;
use UnitEnum;

/**
 * @template TEnumName of UnitEnum
 */
final class EnumReflector implements Reflector
{
    use HasAttributes;

    private array $memoize = [];

    private readonly PHPReflectionEnum $reflectionEnum;

    /**
     * @param class-string<TEnumName>|TEnumName|PHPReflectionEnum<TEnumName> $reflectionEnum
     */
    public function __construct(string|object $reflectionEnum)
    {
        if (is_string($reflectionEnum)) {
            $reflectionEnum = new PHPReflectionEnum($reflectionEnum);
        } elseif ($reflectionEnum instanceof self) {
            $reflectionEnum = $reflectionEnum->getReflection();
        } elseif (! $reflectionEnum instanceof PHPReflectionEnum) {
            $reflectionEnum = new PHPReflectionEnum($reflectionEnum);
        }

        $this->reflectionEnum = $reflectionEnum;
    }

    public function getReflection(): PHPReflectionEnum
    {
        return $this->reflectionEnum;
    }

    /**
     * @return class-string<TEnumName>
     */
    public function getName(): string
    {
        return $this->reflectionEnum->getName();
    }

    public function getShortName(): string
    {
        return $this->reflectionEnum->getShortName();
    }

    public function getFileName(): string|false
    {
        return $this->reflectionEnum->getFileName();
    }

    public function getType(): TypeReflector
    {
        return new TypeReflector($this->reflectionEnum);
    }

    public function isBacked(): bool
    {
        return $this->reflectionEnum->isBacked();
    }

    public function getBackingType(): ?TypeReflector
    {
        $backingType = $this->reflectionEnum->getBackingType();

        if ($backingType === null) {
            return null;
        }

        return new TypeReflector($backingType);
    }

    /**
     * @return UnitEnum[]
     */
    public function getCases(): array
    {
        return $this->memoize(
            key: 'cases',
            closure: fn () => array_map(
                callback: fn (ReflectionEnumUnitCase $case) => $case->getValue(),
                array: $this->getReflectionCases(),
            ),
        );
    }

    /**
     * @return \ReflectionEnumUnitCase[]|\ReflectionEnumBackedCase[]
     */
    public function getReflectionCases(): array
    {
        return $this->reflectionEnum->getCases();
    }

    public function hasCase(string $name): bool
    {
        return $this->reflectionEnum->hasCase($name);
    }

    public function getCase(string $name): UnitEnum
    {
        return $this->reflectionEnum->getCase($name)->getValue();
    }

    public function is(string $className): bool
    {
        return $this->getType()->matches($className);
    }

    public function implements(string $interface): bool
    {
        return $this->getType()->matches($interface);
    }

    private function memoize(string $key, Closure $closure): mixed
    {
        if (! array_key_exists($key, $this->memoize)) {
            $this->memoize[$key] = $closure();
        }

        return $this->memoize[$key];
    }

    public function __serialize(): array
    {
        return ['name' => $this->getName()];
    }

    public function __unserialize(array $data): void
    {
        $this->reflectionEnum = new PHPReflectionEnum($data['name']);
    }
}
