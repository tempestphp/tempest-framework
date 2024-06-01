<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

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
        preg_match('/--(?<name>[\w]+)((?<hasValue>=)\"?(?<value>(.*?))(\"|$))?/', $argument, $matches);

        $name = $matches['name'] ?? null;
        $hasValue = $matches['hasValue'] ?? null;
        $value = $matches['value'] ?? null;

        if (! $hasValue) {
            return [$name, true];
        }

        $value = match ($value) {
            'true' => true,
            'false' => false,
            '' => null,
            default => $value,
        };

        return [$name, $value];
    }
}
