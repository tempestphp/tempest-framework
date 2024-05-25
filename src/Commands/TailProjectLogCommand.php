<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Output\TailReader;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;

final readonly class TailProjectLogCommand
{
    public function __construct(
        private Console $console,
        private LogConfig $logConfig,
    ) {
    }

    #[ConsoleCommand('tail:project', description: 'Tails the project log', aliases: ['tp'])]
    public function __invoke(): void
    {
        foreach ($this->logConfig->channels as $channel) {
            if ($channel instanceof AppendLogChannel) {
                $this->console->writeln("<h1>Log</h1> Listening for logs…");
                (new TailReader())->tail($channel->getPath());
            }
        }

        $this->console->error("No AppendLogChannel registered");
    }
}
