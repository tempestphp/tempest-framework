<?php

declare(strict_types=1);

namespace Tempest\Testing\Console;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleStyle;

final class TestConsoleOutput implements ConsoleOutput
{
    private array $lines = [];
    private array $errorLines = [];
    private array $infoLines = [];
    private array $successLines = [];

    public function write(string $line): void
    {
        $this->lines[] = $line;
    }

    public function writeln(string $line): void
    {
        $this->lines[] = $line;
    }

    public function info(string $line): void
    {
        $this->writeln($line);
        $this->infoLines[] = $line;
    }

    public function error(string $line): void
    {
        $this->writeln($line);
        $this->errorLines[] = $line;
    }

    public function success(string $line): void
    {
        $this->writeln($line);
        $this->successLines[] = $line;
    }

    public function getLinesWithFormatting(): array
    {
        return $this->lines;
    }

    public function getLinesWithoutFormatting(): array
    {
        $pattern = array_map(
            fn (ConsoleStyle $consoleStyle) => ConsoleStyle::ESC->value . $consoleStyle->value,
            ConsoleStyle::cases(),
        );

        return array_map(
            fn (string $line) => str_replace($pattern, '', $line),
            $this->getLinesWithFormatting(),
        );
    }

    public function getTextWithFormatting(): string
    {
        return implode(PHP_EOL, $this->getLinesWithFormatting());
    }

    public function getTextWithoutFormatting(): string
    {
        return implode(PHP_EOL, $this->getLinesWithoutFormatting());
    }

    public function getInfoLines(): array
    {
        return $this->infoLines;
    }

    public function getErrorLines(): array
    {
        return $this->errorLines;
    }

    public function getSuccessLines(): array
    {
        return $this->successLines;
    }
}
