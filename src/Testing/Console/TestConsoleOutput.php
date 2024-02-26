<?php

namespace Tempest\Testing\Console;

use Tempest\Console\ConsoleFormatter;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleStyle;

final class TestConsoleOutput implements ConsoleOutput
{
    private array $formattedLines = [];
    private array $lines = [];
    private array $errorLines = [];
    private array $infoLines = [];
    private array $successLines = [];

    public function __construct(private readonly ConsoleFormatter $formatter)
    {}

    public function write(string $line, ConsoleStyle ...$styles): void
    {
        $this->formattedLines[] = ConsoleStyle::RESET(
            $this->formatter->format($line, ...$styles)
        );

        $this->lines[] = $line;
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

        $this->infoLines[] = $line;
    }

    public function error(string $line): void
    {
        $this->writeln(
            $this->formatter->format($line, ConsoleStyle::FG_RED, ConsoleStyle::BOLD),
        );

        $this->errorLines[] = $line;
    }

    public function success(string $line): void
    {
        $this->writeln($line, ConsoleStyle::FG_GREEN, ConsoleStyle::BOLD);

        $this->successLines[] = $line;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function getFormattedLines(): array
    {
        return $this->formattedLines;
    }

    public function getErrorLines(): array
    {
        return $this->errorLines;
    }

    public function getInfoLines(): array
    {
        return $this->infoLines;
    }

    public function getSuccessLines(): array
    {
        return $this->successLines;
    }

    public function getText(): string
    {
        return implode(PHP_EOL, $this->lines);
    }
}