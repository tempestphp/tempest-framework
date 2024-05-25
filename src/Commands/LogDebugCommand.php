<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Highlight\VarExportLanguage\VarExportLanguage;
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

        $handle = fopen($debugLogPath, "r");

        $this->console->writeln("<h2>Debug</h2> Listening for logs, use <em><strong>lw</strong></em> or <em><strong>ld</strong></em> to start");

        fseek($handle, -1, SEEK_END);
        $offset = ftell($handle);

        /** @phpstan-ignore-next-line */
        while (true) {
            fseek($handle, -1, SEEK_END);
            $newOffset = ftell($handle);

            if ($newOffset <= $offset) {
                continue;
            }

            fseek($handle, $offset);

            $output = $this->highlighter->parse(
                ltrim(fread($handle, $newOffset - $offset)),
                new VarExportLanguage(),
            );

            fwrite(STDOUT, $output . PHP_EOL);

            $offset = $newOffset;
        }
    }
}
