<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionNamedType;
use ReflectionParameter;

final class ConsoleArgumentDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly mixed $default,
        public readonly bool $hasDefault,
        public readonly int $position,
        public readonly ?string $description = null,
        public readonly array $aliases = [],
        public readonly ?string $help = null,
    ) {
    }

    public static function fromParameter(ReflectionParameter $parameter): ConsoleArgumentDefinition
    {
        $attributes = $parameter->getAttributes(ConsoleArgument::class);

        /** @var ?ConsoleArgument $attribute */
        $attribute = ($attributes[0] ?? null)?->newInstance();

        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
        } else {
            $typeName = '';
        }

        return new ConsoleArgumentDefinition(
            name: $parameter->getName(),
            type: $typeName,
            default: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            hasDefault: $parameter->isDefaultValueAvailable(),
            position: $parameter->getPosition(),
            description: $attribute?->description,
            aliases: $attribute->aliases ?? [],
            help: $attribute?->help,
        );
    }

    public function matchesArgument(ConsoleInputArgument $argument): bool
    {
        if ($argument->position === $this->position) {
            return true;
        }

        if (! $argument->name) {
            return false;
        }

        foreach ([$argument->name, ...$this->aliases] as $alias) {
            if ($alias === $argument->name) {
                return true;
            }

            return false;
        }
    }
}
