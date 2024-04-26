<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\Highlight\TempestConsoleLanguage;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\LightTerminalTheme;
use Tempest\Highlight\Themes\TerminalStyle;

final class TestConsoleOutput implements ConsoleOutput
{
    private array $lines = [];
    private array $errorLines = [];
    private array $infoLines = [];
    private array $successLines = [];

    public function write(string $contents): static
    {
        $highlighter = new Highlighter(new LightTerminalTheme());

        $contents = $highlighter->parse($contents, new  TempestConsoleLanguage());

        $this->lines[] = $contents;

        return $this;
    }

    public function writeln(string $line = ''): static
    {
        $highlighter = new Highlighter(new LightTerminalTheme());

        $line = $highlighter->parse($line, new  TempestConsoleLanguage());

        $this->lines[] = $line;

        return $this;
    }

    public function getLinesWithFormatting(): array
    {
        return $this->lines;
    }

    public function getLinesWithoutFormatting(): array
    {
        $pattern = array_map(
            fn (TerminalStyle $consoleStyle) => TerminalStyle::ESC->value . $consoleStyle->value,
            TerminalStyle::cases(),
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

    public function clearLast(): self
    {
        unset($this->lines[array_key_last($this->lines)]);

        return $this;
    }
}
