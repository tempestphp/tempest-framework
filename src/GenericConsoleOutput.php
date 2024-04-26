<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Highlight\TempestConsoleLanguage;
use Tempest\Highlight\Highlighter;

final readonly class GenericConsoleOutput implements ConsoleOutput
{
    public function __construct(private Highlighter $highlighter)
    {
    }

    public function write(string $contents): static
    {
        $this->writeToStdOut($contents);

        return $this;
    }

    public function writeln(string $line = ''): static
    {
        $this->writeToStdOut($line . PHP_EOL);

        return $this;
    }

    private function writeToStdOut(string $content): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite(
            $stdout,
            $this->highlighter->parse($content, new  TempestConsoleLanguage()),
        );

        fclose($stdout);
    }
}
