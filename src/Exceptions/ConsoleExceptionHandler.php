<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleStyle;
use Tempest\ExceptionHandler;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\LightTerminalTheme;
use Throwable;

final readonly class ConsoleExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private Console $console,
    ) {}

    public function handle(Throwable $throwable): void
    {
        $this->console
            ->error($throwable::class)
            ->when(
                expression: $throwable->getMessage(),
                callback: fn (ConsoleOutput $output) => $output->writeln($throwable->getMessage()),
            )
            ->writeln();

        $this->writeSnippet($throwable);

        $this->console
            ->writeln()
            ->writeln($throwable->getFile() . ':' . $throwable->getLine())
            ->writeln();
    }

    private function writeSnippet(Throwable $throwable): void
    {
        $this->console->writeln($this->getCodeSample($throwable));
    }

    private function getCodeSample(Throwable $throwable): string
    {
        $highlighter = (new Highlighter(new LightTerminalTheme()))->withGutter();
        $code = $highlighter->parse(file_get_contents($throwable->getFile()), 'php');
        $lines = explode(PHP_EOL, $code);

        $lines[$throwable->getLine() - 1] = $lines[$throwable->getLine() - 1] . ' ' . ConsoleStyle::BG_RED(ConsoleStyle::FG_WHITE(ConsoleStyle::BOLD(' < ')));
        
        $excerptSize = 5;
        $start = max(0, $throwable->getLine() - $excerptSize);
        $lines = array_slice($lines, $start, $excerptSize * 2);

        return implode(PHP_EOL, $lines);
    }
}
