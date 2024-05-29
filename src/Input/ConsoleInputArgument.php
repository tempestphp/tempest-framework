<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use Tempest\Support\ArrayHelper;

final class ConsoleInputArgument
{
    public function __construct(
        public ?string $name,
        public ?int $position,
        public mixed $value,
        public bool $isPositional = false,
    ) {
    }

    public static function fromString(string $argument, ?int $position = null): ConsoleInputArgument
    {
        if (str_starts_with($argument, '--')) {
            [$key, $value] = self::parseNamedArgument($argument);

            return new ConsoleInputArgument(
                name: $key,
                position: null,
                value: $value,
            );
        } elseif(str_starts_with($argument, '-')) {
            return new ConsoleInputArgument(
                name: $argument,
                position: null,
                value: true,
            );
        } else {
            return new ConsoleInputArgument(
                name: null,
                position: $position,
                value: $argument,
                isPositional: true,
            );
        }
    }

    public function matches(string $name): bool
    {
        if ($this->name === null) {
            return false;
        }

        return ltrim($this->name, '-') === ltrim($name, '-');
    }

    /**
     * @param string $argument
     *
     * @return array{0: string, 1: mixed}
     */
    private static function parseNamedArgument(string $argument): array
    {
        $parts = explode('=', str_replace('--', '', $argument));

        $key = $parts[0];

        $value = $parts[1] ?? true;

        $value = match ($value) {
            'true' => true,
            'false' => false,
            default => $value,
        };

        return [$key, $value];
    }

    public function merge(?ConsoleInputArgument $other): self
    {
        $clone = clone $this;

        if ($other === null) {
            return $clone;
        }

        $clone->value = array_values([...ArrayHelper::wrap($other->value), ...ArrayHelper::wrap($this->value)]);

        return $clone;
    }

    public function asArray(): self
    {
        $clone = clone $this;

        $clone->value = ArrayHelper::wrap($this->value);

        return $clone;
    }
}
