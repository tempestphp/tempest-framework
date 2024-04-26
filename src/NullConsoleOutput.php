<?php

declare(strict_types=1);

namespace Tempest\Console;

final class NullConsoleOutput implements ConsoleOutput
{
    public string $delimiter = PHP_EOL;

    public function write(string $contents): static
    {
        return $this;
    }

    public function writeln(string $line = ''): static
    {
        return $this;
    }
}
