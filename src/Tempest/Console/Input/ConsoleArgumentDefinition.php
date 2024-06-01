<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use ReflectionNamedType;
use ReflectionParameter;
use Tempest\Console\ConsoleArgument;

final readonly class ConsoleArgumentDefinition
{
    public function __construct(
        public string $name,
        public string $type,
        public mixed $default,
        public bool $hasDefault,
        public int $position,
        public ?string $description = null,
        public array $aliases = [],
        public ?string $help = null,
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

        foreach ([$this->name, ...$this->aliases] as $match) {
            if ($argument->matches($match)) {
                return true;
            }
        }

        return false;
    }
}
