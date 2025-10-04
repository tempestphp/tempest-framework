<?php

declare(strict_types=1);

namespace Tempest\Log;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Highlight\LogLanguage\LogLanguage;
use Tempest\Console\Output\TailReader;
use Tempest\Container\Tag;
use Tempest\Highlight\Highlighter;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\Channels\SimpleLogConfig;
use Tempest\Log\LogConfig;
use Tempest\Support\Filesystem;

final readonly class TailLogsCommand
{
    public function __construct(
        private Console $console,
        private LogConfig $config,
        #[Tag('console')]
        private Highlighter $highlighter,
    ) {}

    #[ConsoleCommand('tail:logs', description: 'Tails the project logs', aliases: ['log:tail', 'logs:tail'])]
    public function __invoke(): void
    {
        $appendLogChannel = null;

        foreach ($this->config->logChannels as $channel) {
            if ($channel instanceof AppendLogChannel) {
                $appendLogChannel = $channel;
                break;
            }
        }

        if ($appendLogChannel === null) {
            $this->console->error('Tailing logs is only supported when a <code>AppendLogChannel</code> is configured.');
            return;
        }

        Filesystem\create_file($appendLogChannel->path);

        $this->console->header('Tailing project logs', "Reading <file='{$appendLogChannel->path}'/>…");

        new TailReader()->tail(
            path: $appendLogChannel->path,
            format: fn (string $text) => $this->highlighter->parse($text, new LogLanguage()),
        );
    }
}
