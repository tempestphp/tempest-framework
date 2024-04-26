<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\Highlight\TempestConsoleLanguage;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\TerminalStyle;

final class TestConsoleOutput implements ConsoleOutput
{
    private array $formattedLines = [];
    private array $rawLines = [];

    private array $errorLines = [];
    private array $infoLines = [];
    private array $successLines = [];

    public function __construct(
        private readonly Highlighter $highlighter,
    ) {
    }

    public function write(string $contents): static
    {
        $rawHighlighter = new Highlighter(new RawTerminalTheme());
        $this->rawLines[] = $rawHighlighter->parse($contents, new TempestConsoleLanguage());

        $this->formattedLines[] = $this->highlighter->parse($contents, new TempestConsoleLanguage());

        return $this;
    }

    public function writeln(string $line = ''): static
    {
        $rawHighlighter = new Highlighter(new RawTerminalTheme());
        $this->rawLines[] = $rawHighlighter->parse($line, new TempestConsoleLanguage());

        $this->formattedLines[] = $this->highlighter->parse($line, new TempestConsoleLanguage());

        return $this;
    }

    public function getLinesWithFormatting(): array
    {
        return $this->formattedLines;
    }

    public function getLinesWithoutFormatting(): array
    {
        $pattern = array_map(
            fn (TerminalStyle $consoleStyle) => TerminalStyle::ESC->value . $consoleStyle->value,
            TerminalStyle::cases(),
        );

        return array_map(
            fn (string $line) => str_replace($pattern, '', $line),
            $this->rawLines,
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
        unset($this->formattedLines[array_key_last($this->formattedLines)]);

        return $this;
    }
}
