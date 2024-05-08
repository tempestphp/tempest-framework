<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;
use Tempest\Container\Container;

final readonly class RenderConsoleRescueScreen
{
    public function __construct(
        private Container $container,
        private ConsoleConfig $consoleConfig,
        private Console $console,
    ) {
    }

    public function __invoke(string $commandName): void
    {
        $this->console->writeln("<error>Command {$commandName} not found</error>");

        $similarCommands = $this->getSimilarCommands($commandName);

        if ($similarCommands === []) {
            return;
        }

        if (count($similarCommands) === 1) {
            if ($this->console->confirm("Did you mean {$similarCommands[0]}?")) {
                $this->runIntendedCommand($similarCommands[0]);
            }
        } else {
            $intendedCommand = $this->console->ask(
                'Did you mean to run one of these?',
                options: $similarCommands,
            );

            $this->runIntendedCommand($intendedCommand);
        }
    }

    private function getSimilarCommands(string $name): array
    {
        $similarCommands = [];

        foreach ($this->consoleConfig->commands as $consoleCommand) {
            if (str_starts_with($consoleCommand->getName(), $name)) {
                $similarCommands[] = $consoleCommand->getName();

                continue;
            }

            $levenshtein = levenshtein($name, $consoleCommand->getName());

            if ($levenshtein <= 3) {
                $similarCommands[] = $consoleCommand->getName();
            }
        }

        return $similarCommands;
    }

    private function runIntendedCommand(string $commandName): void
    {
        ($this->container->get(ExecuteConsoleCommand::class))($commandName);
    }
}
