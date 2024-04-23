<?php

declare(strict_types=1);

namespace Tempest\Console;

final class ConsoleInputArgument
{
    public function __construct(
        public ?string $name,
        public ?int $position,
        public mixed $value,
        public bool $isPositional = false,
    ) {
    }

    public static function fromString(string $argument, int $position): ConsoleInputArgument
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
}
