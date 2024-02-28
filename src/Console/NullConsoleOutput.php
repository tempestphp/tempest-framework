<?php

declare(strict_types=1);

namespace Tempest\Console;

final readonly class NullConsoleOutput implements ConsoleOutput
{
    public function write(string $line): void
    {
        return;
    }

    public function writeln(string $line): void
    {
        return;
    }

    public function info(string $line): void
    {
        return;
    }

    public function error(string $line): void
    {
        return;
    }

    public function success(string $line): void
    {
        return;
    }
}
