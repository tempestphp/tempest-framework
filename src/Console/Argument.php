<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;

readonly class Argument
{
    /**
     * @param string $name
     * @param mixed $value
     * @param string[] $aliases
     * @param null|string $description
     * @param null|ReflectionParameter $parameter
     */
    public function __construct(
        public string $name,
        public mixed $value,
        public mixed $default,
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
            $this->default,
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
            default: $value,
            aliases: array_slice($names, 1),
            description: $description,
            parameter: $parameter,
        );
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getDefaultValue(): mixed
    {
        return $this->default;
    }

    // todo allow user to customize this via attribute
    public function getHelp(): string
    {
        return $this->description;
    }
}
