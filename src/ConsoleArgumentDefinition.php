<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;

final class ConsoleArgumentDefinition
{

    public readonly array $help;

    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly mixed $default,
        public readonly int $position,
        public readonly ?string $description = null,
        public readonly array $aliases = [],
        string|array $help = [],
    )
    {
        $this->help = $help;
    }

    public static function fromParameter(ReflectionParameter $parameter): ConsoleArgumentDefinition
    {
        $attributes = $parameter->getAttributes(ConsoleArgument::class);

        /** @var ?ConsoleArgument $attribute */
        $attribute = ($attributes[0] ?? null)?->newInstance();

        return new ConsoleArgumentDefinition(
            name: $parameter->getName(),
            type: $parameter->getType()->getName(),
            default: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            position: $parameter->getPosition(),
            description: $attribute?->description,
            aliases: $attribute->aliases ?? [],
            help: $attribute?->help ?? [],
        );
    }

}
