<?php

namespace Tests\Tempest;

use Tempest\Console\BaseConsoleOutput;
use Tempest\Console\ConsoleStyle;
use Tempest\Interface\ConsoleOutput;

final class TestConsoleOutput implements ConsoleOutput
{
    public array $lines = [];

    use BaseConsoleOutput;

    public function writeln(string $line, ConsoleStyle ...$styles): void
    {
        $this->lines[] = $line;
    }
}