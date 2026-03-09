<?php

declare(strict_types=1);

namespace Tempest\Debug;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Output\TailReader;
use Tempest\Support\Filesystem;

final readonly class TailDebugCommand
{
    public function __construct(
        private Console $console,
        private DebugConfig $debugConfig,
    ) {}

    #[ConsoleCommand('tail:debug', description: 'Tails the debug log', aliases: ['debug:tail'])]
    public function __invoke(bool $clear = true): void
    {
        $debugLogPath = $this->debugConfig->logPath;

        if (! $debugLogPath) {
            $this->console->error('No debug log configured in <code>DebugConfig</code>.');

            return;
        }

        if ($clear && Filesystem\is_file($debugLogPath)) {
            Filesystem\delete_file($debugLogPath);
        }

        Filesystem\create_file($debugLogPath);

        $this->console->header('Tailing debug logs', "Reading <file='{$debugLogPath}'/>…");

        new TailReader()->tail($debugLogPath);
    }
}
