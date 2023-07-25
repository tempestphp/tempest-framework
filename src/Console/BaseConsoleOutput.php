<?php

namespace Tempest\Console;

use Tempest\Interface\ConsoleFormatter;

trait BaseConsoleOutput
{
    public function __construct(
        private readonly ConsoleFormatter $formatter
    ) {}

    public function writeln(string $line): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite($stdout, $line . PHP_EOL);

        fclose($stdout);
    }

    public function info(string $line): void
    {
        $this->writeln($line);
    }

    public function error(string $line): void
    {
        $this->writeln(
            $this->formatter->format($line, ConsoleStyle::FG_RED, ConsoleStyle::BOLD)
        );
    }

    public function success(string $line): void
    {
        $this->writeln(
            $this->formatter->format($line, ConsoleStyle::FG_GREEN, ConsoleStyle::BOLD)
        );
    }
}