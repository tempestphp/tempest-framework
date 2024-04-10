<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ConsoleInput
{
    public function readln(): string;

    public function ask(
        string $question,
        ?array $options = null,
    ): string;

    public function confirm(
        string $question,
        bool $default = false,
    ): bool;
}
