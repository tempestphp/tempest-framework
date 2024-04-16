<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\AppConfig;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutput;

final readonly class RenderConsoleCommandOverview
{
    public function __construct(
        private ConsoleOutput $output,
        private AppConfig $appConfig,
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function __invoke(): void
    {
        $this->output
            ->writeln("<h1>{$this->consoleConfig->name}</h1>")
            ->when(
                expression: $this->appConfig->discoveryCache,
                callback: fn (ConsoleOutput $output) => $output->error('Discovery cache is enabled!')
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
            $this->output
                ->writeln()
                ->writeln('<h2>' . ucfirst($group) . '</h2>');

            foreach ($commandsForGroup as $consoleCommand) {
                (new RenderConsoleCommand($this->output))($consoleCommand);
            }
        }
    }
}
