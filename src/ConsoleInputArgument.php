<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;

final class ConsoleInputArgument
{
    /**
     * @param string|int $name
     * @param mixed $value
     * @param mixed $default
     * @param string[] $aliases
     * @param null|string $description
     * @param string[] $help
     * @param null|ReflectionParameter $parameter
     */
    public function __construct(
        public string|int $name,
        public mixed $value,
        public mixed $default,
        public int $position,
        public array $aliases = [],
        public ?string $description = null,
        public array $help = [],
        public ?ReflectionParameter $parameter = null,
    ) {
    }

    public static function fromParameter(ReflectionParameter $parameter): self
    {
        return new ConsoleInputArgument(
            name: $parameter->getName(),
            value: $parameter->isDefaultValueAvailable()
                ? $parameter->getDefaultValue()
                : null,
            default: $parameter->isDefaultValueAvailable()
                ? $parameter->getDefaultValue()
                : null,
            position: $parameter->getPosition(),
            aliases: self::getAliases($parameter),
            description: $parameter->getName(),
            help: self::getHelpLines($parameter),
            parameter: $parameter,
        );
    }

    /**
     * @return string[]
     */
    private static function getHelpLines(ReflectionParameter $parameter): array
    {
        if (! $attribute = $parameter->getAttributes(ConsoleArgument::class)[0] ?? null) {
            return [];
        }

        return $attribute->newInstance()->getHelpLines();
    }

    /**
     * @return string[]
     */
    private static function getAliases(ReflectionParameter $parameter): array
    {
        if (! $attribute = $parameter->getAttributes(ConsoleArgument::class)[0] ?? null) {
            return [];
        }

        return $attribute->newInstance()->getAliases();
    }

    public function withValue(mixed $value): ConsoleInputArgument
    {
        $new = clone $this;
        $new->value = $value;

        return $new;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getName(): string
    {
        if (is_numeric($this->name)) {
            return $this->getValue();
        }

        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getAllNames(): array
    {
        return [$this->name, ...$this->aliases];
    }
}
