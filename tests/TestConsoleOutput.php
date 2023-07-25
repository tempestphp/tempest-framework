<?php

namespace Tests\Tempest;

use Tempest\Console\BaseConsoleOutput;
use Tempest\Interface\ConsoleOutput;

final class TestConsoleOutput implements ConsoleOutput
{
    public array $lines = [];

    use BaseConsoleOutput;

    public function writeln(string $line): void
    {
        $this->lines[] = $line;
    }
}