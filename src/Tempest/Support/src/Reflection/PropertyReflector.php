<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Error;
use ReflectionProperty as PHPReflectionProperty;

final readonly class PropertyReflector implements Reflector
{
    use HasAttributes;

    public function __construct(
        private PHPReflectionProperty $reflectionProperty,
    ) {
    }

    public static function fromParts(string|object $class, string $name): self
    {
        return new self(new PHPReflectionProperty($class, $name));
    }

    public function getReflection(): PHPReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function getValue(object $object): mixed
    {
        return $this->reflectionProperty->getValue($object);
    }

    public function setValue(object $object, mixed $value): void
    {
        $this->reflectionProperty->setValue($object, $value);
    }

    public function isInitialized(object $object): bool
    {
        return $this->reflectionProperty->isInitialized($object);
    }

    public function accepts(mixed $input): bool
    {
        return $this->getType()->accepts($input);
    }

    public function getClass(): ClassReflector
    {
        return new ClassReflector($this->reflectionProperty->getDeclaringClass());
    }

    public function getType(): ?TypeReflector
    {
        return new TypeReflector($this->reflectionProperty);
    }

    public function isIterable(): bool
    {
        return $this->getType()->isIterable();
    }

    public function isPromoted(): bool
    {
        return $this->reflectionProperty->isPromoted();
    }

    public function getIterableType(): ?TypeReflector
    {
        $doc = $this->reflectionProperty->getDocComment();

        if (! $doc) {
            return null;
        }

        preg_match('/@var ([\\\\\w]+)\[]/', $doc, $match);

        if (! isset($match[1])) {
            return null;
        }

        return new TypeReflector(ltrim($match[1], '\\'));
    }

    public function isUninitialized(object $object): bool
    {
        return ! $this->reflectionProperty->isInitialized($object);
    }

    public function unset(object $object): void
    {
        unset($object->{$this->getName()});
    }

    public function set(object $object, mixed $value): void
    {
        $this->reflectionProperty->setValue($object, $value);
    }

    public function get(object $object, mixed $default = null): mixed
    {
        try {
            return $this->reflectionProperty->getValue($object) ?? $default;
        } catch (Error $error) {
            return $default ?? throw $error;
        }
    }

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    public function hasDefaultValue(): bool
    {
        $constructorParameters = [];

        foreach (($this->getClass()->getConstructor()?->getParameters() ?? []) as $parameter) {
            $constructorParameters[$parameter->getName()] = $parameter;
        }

        $hasDefaultValue = $this->reflectionProperty->hasDefaultValue();

        $hasPromotedDefaultValue = $this->isPromoted()
            && $constructorParameters[$this->getName()]->isDefaultValueAvailable();

        return $hasDefaultValue || $hasPromotedDefaultValue;
    }
}
