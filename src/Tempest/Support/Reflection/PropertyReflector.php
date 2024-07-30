<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use ReflectionProperty as PHPReflectionProperty;

final readonly class PropertyReflector implements Reflector
{
    public function __construct(
        private PHPReflectionProperty $reflectionProperty
    ) {
    }

    public function accepts(mixed $input): bool
    {
        return $this->getType()->accepts($input);
    }

    public function getType(): ?TypeReflector
    {
        return new TypeReflector($this->reflectionProperty);
    }

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }
}
