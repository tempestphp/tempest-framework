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

    public function getReflection(): PHPReflectionProperty
    {
        return $this->reflectionProperty;
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
        } catch (Error $e) {
            return $default ?? throw $e;
        }
    }

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }
}
