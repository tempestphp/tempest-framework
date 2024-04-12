<?php

declare(strict_types=1);

namespace Tempest\Console;

final readonly class NullConsoleInput implements ConsoleInput
{
    public function readln(): string
    {
        return '';
    }

    public function read(int $bytes): string
    {
        return '';
    }
}
