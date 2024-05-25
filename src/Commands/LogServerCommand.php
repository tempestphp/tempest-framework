<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Output\TailReader;
use Tempest\Log\LogConfig;

final readonly class LogServerCommand
{
    public function __construct(
        private Console $console,
        private LogConfig $logConfig,
    ) {
    }

    #[ConsoleCommand('log:server', aliases: ['ls'])]
    public function __invoke(): void
    {
        $serverLogPath = $this->logConfig->serverLogPath;

        if (! $serverLogPath) {
            $this->console->error("No server log configured in LogConfig");

            return;
        }

        if (! file_exists($serverLogPath)) {
            $this->console->error("No valid server log at <em>{$serverLogPath}</em>");

            return;
        }

        $this->console->writeln("<h1>Server</h1> Listening for logs…");

        (new TailReader())->tail($serverLogPath);
    }
}
