<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\ConsoleFormatter;

trait BaseConsoleOutput
{
    public function __construct(
        private readonly ConsoleFormatter $formatter,
    ) {
    }

    public function writeln(string $line, ConsoleStyle ...$styles): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite(
            $stdout,
            $this->formatter->format($line . PHP_EOL, ...$styles),
        );

        fclose($stdout);
    }

    public function info(string $line): void
    {
        $this->writeln($line);
    }

    public function error(string $line): void
    {
        $this->writeln(
            $this->formatter->format($line, ConsoleStyle::FG_RED, ConsoleStyle::BOLD),
        );
    }

    public function success(string $line): void
    {
        $this->writeln($line, ConsoleStyle::FG_GREEN, ConsoleStyle::BOLD);
    }
}
