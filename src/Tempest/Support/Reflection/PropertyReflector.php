<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionProperty as PHPReflectionProperty;

final readonly class PropertyReflector implements Reflector
{
    use HasAttributes;

    public function __construct(
        private PHPReflectionProperty $reflectionProperty,
    ) {}

    public function getReflection(): PHPReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function accepts(mixed $input): bool
    {
        return $this->getType()->accepts($input);
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

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }
}
