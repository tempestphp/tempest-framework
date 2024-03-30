<?php

declare(strict_types=1);

namespace Tempest\Console;

trait HandlesConsoleOutput
{
    public function write(string $line): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite(
            $stdout,
            ConsoleStyle::RESET($line),
        );

        fclose($stdout);
    }

    public function writeln(string $line): void
    {
        $this->write($line . PHP_EOL);
    }

    public function info(string $line): void
    {
        $this->writeln(
            ConsoleStyle::FG_DARK_BLUE($line)
        );
    }

    public function error(string $line): void
    {
        $this->writeln(
            ConsoleStyle::BOLD(ConsoleStyle::BG_RED(ConsoleStyle::FG_WHITE($line))),
        );
    }

    public function success(string $line): void
    {
        $this->writeln(
            ConsoleStyle::BOLD(ConsoleStyle::FG_GREEN($line))
        );
    }
}
