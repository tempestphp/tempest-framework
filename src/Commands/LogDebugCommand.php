<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Highlight\VarExportLanguage\VarExportLanguage;
use Tempest\Console\Output\TailReader;
use Tempest\Highlight\Highlighter;
use Tempest\Log\LogConfig;

final readonly class LogDebugCommand
{
    public function __construct(
        private Console $console,
        private LogConfig $logConfig,
        private Highlighter $highlighter,
    ) {
    }

    #[ConsoleCommand('log:debug', aliases: ['ld'])]
    public function __invoke(): void
    {
        $debugLogPath = $this->logConfig->debugLogPath;

        if (! $debugLogPath) {
            $this->console->error("No debug log configured in LogConfig");

            return;
        }

        $dir = pathinfo($debugLogPath, PATHINFO_DIRNAME);

        if (! is_dir($dir)) {
            mkdir($dir);
        }

        if (! file_exists($debugLogPath)) {
            touch($debugLogPath);
        }

        $this->console->writeln("<h1>Debug</h1> Listening for logs, use <em><strong>ll()</strong></em>, <em><strong>lw()</strong></em>, or <em><strong>ld()</strong></em>");

        (new TailReader())->tail(
            $debugLogPath,
            fn (string $text) => $this->highlighter->parse(
                $text,
                new VarExportLanguage(),
            )
        );
    }
}
