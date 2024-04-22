<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Highlight\TempestConsoleLanguage;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\LightTerminalTheme;

final class GenericConsoleOutput implements ConsoleOutput
{
    public string $delimiter = PHP_EOL;

    public function delimiter(string $delimiter): ConsoleOutput
    {
        $clone = clone $this;

        $clone->delimiter = $delimiter;

        return $clone;
    }

    public function write(string $contents): ConsoleOutput
    {
        $this->writeToStdOut($contents);

        return $this;
    }

    public function writeln(string $line = ''): ConsoleOutput
    {
        $this->writeToStdOut($line . $this->delimiter);

        return $this;
    }

    public function info(string $line): ConsoleOutput
    {
        $this->writeln("<em>{$line}</em>");

        return $this;
    }

    public function error(string $line): ConsoleOutput
    {
        $this->writeln("<error>{$line}</error>");

        return $this;
    }

    public function success(string $line): ConsoleOutput
    {
        $this->writeln("<success>{$line}</success>");

        return $this;
    }

    public function when(mixed $expression, callable $callback): ConsoleOutput
    {
        if ($expression) {
            $callback($this);
        }

        return $this;
    }

    private function writeToStdOut(string $content): void
    {
        $highlighter = new Highlighter(new LightTerminalTheme());

        $stdout = fopen('php://stdout', 'w');

        fwrite(
            $stdout,
            $highlighter->parse($content, new  TempestConsoleLanguage()),
        );

        fclose($stdout);
    }
}
