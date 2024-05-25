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
        $appendLogChannel = null;

        foreach ($this->logConfig->channels as $channel) {
            if ($channel instanceof AppendLogChannel) {
                $appendLogChannel = $channel;

                break;
            }
        }

        $this->console->write('<h1>Log</h1> ');

        if (! $appendLogChannel) {
            $this->console->error("No AppendLogChannel registered");

            return;
        }

        $dir = pathinfo($appendLogChannel->getPath(), PATHINFO_DIRNAME);

        if (! is_dir($dir)) {
            mkdir($dir);
        }

        if (! file_exists($appendLogChannel->getPath())) {
            touch($appendLogChannel->getPath());
        }

        $this->console->writeln("Listening at <em>{$appendLogChannel->getPath()}</em>");

        (new TailReader())->tail($appendLogChannel->getPath());
    }
}
