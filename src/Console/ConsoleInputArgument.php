<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;
use function Tempest\attribute;

final class ConsoleInputArgument
{
    /**
     * @param string $name
     * @param mixed $value
     * @param mixed $default
     * @param string[] $aliases
     * @param null|string $description
     * @param string[] $help
     * @param null|ReflectionParameter $parameter
     */
    public function __construct(
        public string $name,
        public mixed $value,
        public mixed $default,
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
            aliases: self::getAliases($parameter),
            description: $parameter->getName(),
            help: self::getHelpLines($parameter),
            parameter: $parameter,
        );
    }

    /**
     * @return string[]
     */
    public static function getHelpLines(ReflectionParameter $parameter): array
    {
        if (! $attribute = attribute(ConsoleArgument::class)->in($parameter)->first()) {
            return [];
        }

        return $attribute->getHelpLines();
    }

    /**
     * @return string[]
     */
    public static function getAliases(ReflectionParameter $parameter): array
    {
        if (! $attribute = attribute(ConsoleArgument::class)->in($parameter)->first()) {
            return [];
        }

        return $attribute->getAliases();
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

    /**
     * @return string[]
     */
    public function getAllNames(): array
    {
        return [$this->name, ...$this->aliases];
    }
}
