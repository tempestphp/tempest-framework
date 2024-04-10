<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleInput;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleOutputType;

final class CommandNotFoundException extends ConsoleException
{
    public function __construct(
        private readonly string $commandName,
        private ConsoleConfig $consoleConfig,
        private ConsoleInput $input,
    ) {
        parent::__construct();
    }

    public function render(ConsoleOutput $output): void
    {
        $similarCommands = $this->getSimilarCommands();

        $output->writeln(
            sprintf('Command %s not found', $this->commandName),
            ConsoleOutputType::ERROR,
        )
            ->when(
                expression: count($similarCommands) > 0,
                callback: function (ConsoleOutput $output) use ($similarCommands) {
                    $output->writeln('Did you mean one of these?', ConsoleOutputType::INFO)
                        ->writeln();

                    foreach ($similarCommands as $index => $similarCommand) {
                        $output->delimiter(' ')
                            ->write("[$index] ", ConsoleOutputType::INFO)
                            ->write($similarCommand->getName())
                            ->writeln()
                            ->writeln();
                    }

                    $intendedIdx = $this->input->ask(
                        'Select intended command:',
                        options: array_keys($similarCommands),
                    );

                    $intendedCommand = $similarCommands[$intendedIdx];

                    throw MistypedCommandException::for($intendedCommand);
                }
            );
    }

    private function getSimilarCommands(): array
    {
        $similarCommands = [];


        foreach ($this->consoleConfig->commands as $consoleCommand) {
            $levenshtein = levenshtein($this->commandName, $consoleCommand->getName());

            if ($levenshtein <= 3) {
                $similarCommands[] = $consoleCommand;
            }
        }

        return $similarCommands;
    }
}
