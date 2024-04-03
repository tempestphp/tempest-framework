<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\AppConfig;

final readonly class RenderConsoleCommandOverviewMessage
{
    public function __construct(
        private AppConfig $appConfig,
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function __invoke(): string
    {
        $builder = ConsoleOutputBuilder::new()
            ->withDefaultBranding()
            ->when(
                $this->appConfig->discoveryCache,
                fn (ConsoleOutputBuilder $builder) => $builder->error(' Discovery cache is enabled! ')->blank()
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
            $builder->formatted(ConsoleStyle::BOLD(ConsoleStyle::BG_BLUE(' ' . ucfirst($group) . ' ')));

            foreach ($commandsForGroup as $consoleCommand) {
                $renderedConsoleCommand = (new RenderConsoleCommandMessage())($consoleCommand);
                $builder->formatted("  $renderedConsoleCommand");
            }

            $builder->blank();
        }

        return $builder->toString();
    }
}
