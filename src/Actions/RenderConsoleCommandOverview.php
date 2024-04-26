<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\AppConfig;
use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;

final readonly class RenderConsoleCommandOverview
{
    public function __construct(
        private Console $console,
        private AppConfig $appConfig,
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function __invoke(): void
    {
        $this->console
            ->writeln("<h1>{$this->consoleConfig->name}</h1>")
            ->when(
                expression: $this->appConfig->discoveryCache,
                callback: fn (Console $console) => $console->error('Discovery cache is enabled!')
            );

        /** @var \Tempest\Console\ConsoleCommand[][] $commands */
        $commands = [];

        foreach ($this->consoleConfig->commands as $consoleCommand) {
            $parts = explode(':', $consoleCommand->getName());

            $group = count($parts) > 1 ? $parts[0] : 'General';

            $commands[$group][$consoleCommand->getName()] = $consoleCommand;
        }

        ksort($commands);

        foreach ($commands as $group => $commandsForGroup) {
            $this->console
                ->writeln()
                ->writeln('<h2>' . ucfirst($group) . '</h2>');

            foreach ($commandsForGroup as $consoleCommand) {
                (new RenderConsoleCommand($this->console))($consoleCommand);
            }
        }
    }
}
