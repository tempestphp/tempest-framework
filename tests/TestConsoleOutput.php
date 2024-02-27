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

    public function write(string $line): void
    {
        $this->lines[] = $line;
    }

    public function writeln(string $line): void
    {
        $this->lines[] = $line;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function getLinesWithoutFormatting(): array
    {
        $pattern = array_map(
            fn (ConsoleStyle $consoleStyle) => ConsoleStyle::ESC->value . $consoleStyle->value,
            ConsoleStyle::cases()
        );

        return array_map(
            fn (string $line) => str_replace($pattern, '', $line),
            $this->getLines(),
        );
    }

    public function asText(): string
    {
        return implode(PHP_EOL, $this->lines);
    }

    public function asTextWithoutFormatting(): string
    {
        return implode(PHP_EOL, $this->lines);
    }
}
