<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\AppConfig;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleOutputType;

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
            ->writeln($this->consoleConfig->name, ConsoleOutputType::H1)
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
                ->writeln(ucfirst($group), ConsoleOutputType::H2);

            foreach ($commandsForGroup as $consoleCommand) {
                (new RenderConsoleCommand($this->output))($consoleCommand);
            }
        }
    }
}
