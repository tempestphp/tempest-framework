<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutputType;

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
        $similarCommands = $this->getSimilarCommands();

        $console->writeln(
            sprintf('Command %s not found', $this->commandName),
            ConsoleOutputType::ERROR,
        );

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

    private function getSimilarCommands(): array
    {
        $similarCommands = [];

        foreach ($this->consoleConfig->commands as $consoleCommand) {
            $levenshtein = levenshtein($this->commandName, $consoleCommand->getName());

            if ($levenshtein <= 3) {
                $similarCommands[] = $consoleCommand->getName();
            }
        }

        return $similarCommands;
    }
}
