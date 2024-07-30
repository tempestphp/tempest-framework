<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionParameter;

final readonly class ParameterReflector implements Reflector
{
    public function __construct(
        private ReflectionParameter $reflectionParameter,
    ) {
    }

    /**
     * @template TAttributeClass of object
     * @param class-string<TAttributeClass> $attributeClass
     * @return TAttributeClass|null
     */
    public function getAttribute(string $attributeClass): object|null
    {
        $attribute = $this->reflectionParameter->getAttributes($attributeClass)[0] ?? null;

        return $attribute?->newInstance();
    }

    public function getReflection(): ReflectionParameter
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
