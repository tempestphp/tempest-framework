<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;

final class CommandNotFoundException extends ConsoleException
{
    public function __construct(
        private readonly string $commandName,
        private readonly ConsoleConfig $consoleConfig,
    ) {
        parent::__construct();
    }

    public function render(Console $console): void
    {
        $similarCommands = $this->getSimilarCommands($this->commandName);

        $console->writeln(
            sprintf('<error>Command %s not found</error>', $this->commandName),
        );

        if ($similarCommands === []) {
            return;
        }

        if (count($similarCommands) === 1) {
            if ($console->confirm("Did you mean {$similarCommands[0]}?")) {
                throw new MistypedCommandException($similarCommands[0]);
            }

            return;
        }

        $intendedCommand = $console->ask(
            'Did you mean to run one of these?',
            options: $similarCommands,
        );

        throw new MistypedCommandException($intendedCommand);
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
}
