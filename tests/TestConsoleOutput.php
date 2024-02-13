<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Console\BaseConsoleOutput;
use Tempest\Console\ConsoleStyle;
use Tempest\Interface\ConsoleOutput;

final class TestConsoleOutput implements ConsoleOutput
{
    use BaseConsoleOutput;

    public array $lines = [];

    public function writeln(string $line, ConsoleStyle ...$styles): void
    {
        $this->lines[] = $line;
    }
}
