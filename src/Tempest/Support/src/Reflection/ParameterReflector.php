<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionParameter as PHPReflectionParameter;

final readonly class ParameterReflector implements Reflector
{
    use HasAttributes;

    public function __construct(
        private PHPReflectionParameter $reflectionParameter,
    ) {
    }

    public function getReflection(): PHPReflectionParameter
    {
        return $this->reflectionParameter;
    }

    public function getName(): string
    {
        return $this->reflectionParameter->getName();
    }

    public function getType(): TypeReflector
    {
        return new TypeReflector($this->reflectionParameter);
    }

    public function isOptional(): bool
    {
        return $this->reflectionParameter->isOptional();
    }

    public function isDefaultValueAvailable(): bool
    {
        return $this->reflectionParameter->isDefaultValueAvailable();
    }

    public function getPosition(): int
    {
        return $this->reflectionParameter->getPosition();
    }

    public function hasDefaultValue(): bool
    {
        return $this->reflectionParameter->isDefaultValueAvailable();
    }

    public function getDefaultValue(): mixed
    {
        return $this->reflectionParameter->getDefaultValue();
    }

    public function isVariadic(): bool
    {
        return $this->reflectionParameter->isVariadic();
    }

    public function isIterable(): bool
    {
        return $this->getType()->isIterable();
    }

    public function isRequired(): bool
    {
        return ! $this->reflectionParameter->allowsNull()
            && ! $this->reflectionParameter->isOptional();
    }
}
