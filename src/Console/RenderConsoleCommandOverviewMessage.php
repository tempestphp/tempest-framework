<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\AppConfig;

final readonly class RenderConsoleCommandOverviewMessage
{
    public function __construct(
        private AppConfig $appConfig,
        private ConsoleConfig $consoleConfig,
        private ConsoleOutputBuilder $outputBuilder,
    ) {
    }

    public function __invoke(): string
    {
        $builder = $this->outputBuilder
            ->header("Tempest")
            ->when(
                $this->appConfig->discoveryCache,
                fn (ConsoleOutputBuilder $builder) => $builder->info(' Discovery cache is enabled! ')->blank()
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
            $builder->label($group);

            foreach ($commandsForGroup as $consoleCommand) {
                $renderedConsoleCommand = (new RenderConsoleCommandMessage())($consoleCommand);
                $builder->raw("  $renderedConsoleCommand");
            }

            $builder->blank();
        }

        return $builder->toString();
    }
}
