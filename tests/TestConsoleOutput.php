<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\HandlesConsoleOutput;

final class TestConsoleOutput implements ConsoleOutput
{
    use HandlesConsoleOutput;

    public array $lines = [];

    public function writeln(string $line, ConsoleStyle ...$styles): void
    {
        $this->lines[] = $line;
    }

    public function asText(): string
    {
        return implode(PHP_EOL, $this->lines);
    }
}
