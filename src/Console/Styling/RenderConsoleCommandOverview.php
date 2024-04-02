<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

use Tempest\AppConfig;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\RenderConsoleCommand;

final readonly class RenderConsoleCommandOverview
{
    public function __construct(
        private AppConfig $appConfig,
        private ConsoleConfig $consoleConfig,
        private RenderConsoleCommand $renderrer,
    ) {
    }

    public function __invoke(): string
    {
        $commands = $this->getCommands();

        $response = ConsoleOutputBuilder::new()
            ->brand(' Tempest Console ')
            ->blank()
            ->info(sprintf("Explore %s the available commands by running one of the following:", count($commands)))
            ->blank()
            ->when(
                $this->appConfig->discoveryCache,
                fn (ConsoleOutputBuilder $builder) => $builder->error('Discovery cache is enabled!'),
            );

        foreach ($commands as $group => $commandsForGroup) {
            if (! $commandsForGroup) {
                continue;
            }

            $response->brand($group);

            foreach ($commandsForGroup as $consoleCommand) {
                if ($consoleCommand->isHidden()) {
                    continue;
                }

                $response->formatted(
                    " " . $this->renderCommand($consoleCommand)
                );
            }

            $response->blank();
        }

        return $response->toString();
    }

    /**
     * @return ConsoleCommand[][]
     */
    private function getCommands(): array
    {
        $commands = [];

        /**
         * Groups commands into namespaces by the first part of the command name.
         */
        foreach ($this->consoleConfig->commands as $consoleCommand) {
            if ($consoleCommand->isHidden()) {
                continue;
            }

            $group = $consoleCommand->getGroup();

            $commands[$group][$consoleCommand->getName()] = $consoleCommand;
        }

        ksort($commands);

        return $commands;
    }

    private function renderCommand(ConsoleCommand $consoleCommand): string
    {
        return $this->renderrer->__invoke($consoleCommand);
    }
}
