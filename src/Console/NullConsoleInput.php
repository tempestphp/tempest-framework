<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\ConsoleInput;

final readonly class NullConsoleInput implements ConsoleInput
{
    public function readln(): string
    {
        return '';
    }

    public function ask(string $question, ?array $options = null, ?string $default = null): string
    {
        return '';
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $default;
    }
}
