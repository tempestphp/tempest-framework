<?php

declare(strict_types=1);

namespace Tempest\Console;

final class ConsoleInputArgument
{
    public function __construct(
        public ?string $name,
        public mixed $value,
        public int $position,
    ) {
    }

    public static function fromString(string $argument, int $position): ConsoleInputArgument
    {
        if (str_starts_with($argument, '--')) {
            [$key, $value] = self::parseNamedArgument($argument);

            return new ConsoleInputArgument(
                name: $key,
                value: $value,
                position: $position,
            );
        } else {
            return new ConsoleInputArgument(
                name: null,
                value: $argument,
                position: $position,
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
