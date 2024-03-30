<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;

readonly class Argument
{
    public function __construct(
        public string $name,
        public mixed $value,
        public array $aliases = [],
        public ?string $description = null,
        public ?ReflectionParameter $parameter = null,
    ) {

    }

    public function withValue(mixed $value): static
    {
        return new static(
            $this->name,
            $value instanceof self ? $value->getValue() : $value,
            $this->aliases,
            $this->description,
            $this->parameter,
        );
    }

    public static function new(array $names, string $description, mixed $value = null, ?ReflectionParameter $parameter = null): static
    {
        return new static(
            name: $names[0],
            value: $value,
            aliases: array_slice($names, 1),
            description: $description,
            parameter: $parameter,
        );
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    // todo allow user to customize this via attribute
    public function getHelp(): string
    {
        return $this->description;
    }

}
