<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;

final readonly class LogProjectCommand
{
    public function __construct(
        private Console $console,
        private LogConfig $logConfig,
    ) {
    }

    #[ConsoleCommand('log:project', aliases: ['lp'])]
    public function __invoke(): void
    {
        foreach ($this->logConfig->channels as $channel) {
            if ($channel instanceof AppendLogChannel) {
                passthru("tail -f {$channel->getPath()}");
            }
        }

        $this->console->error("No AppendLogChannel registered");
    }
}
