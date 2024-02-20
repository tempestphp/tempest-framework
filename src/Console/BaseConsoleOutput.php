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

    public function write(string $line, ConsoleStyle ...$styles): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite(
            $stdout,
            ConsoleStyle::RESET($this->formatter->format($line, ...$styles)),
        );

        fclose($stdout);
    }

    public function writeln(string $line, ConsoleStyle ...$styles): void
    {
        $this->write($line . PHP_EOL, ...$styles);
    }

    public function info(string $line): void
    {
        $this->writeln(
            $this->formatter->format($line, ConsoleStyle::FG_DARK_BLUE)
        );
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
