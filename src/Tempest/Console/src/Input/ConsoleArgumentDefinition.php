<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use Tempest\Console\ConsoleArgument;
use Tempest\Support\Reflection\ParameterReflector;

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

    public static function fromParameter(ParameterReflector $parameter): ConsoleArgumentDefinition
    {
        $attribute = $parameter->getAttribute(ConsoleArgument::class);

        $type = $parameter->getType();

        return new ConsoleArgumentDefinition(
            name: $parameter->getName(),
            type: $type->getName(),
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
