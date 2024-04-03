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

    /**
     * @return string[]
     */
    public function getHelpLines(): array
    {
        if ($this->parameter && $attribute = attribute(ConsoleArgument::class)->in($this->parameter)->first()) {
            return $attribute->getHelpLines();
        }

        return [$this->description];
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
}
