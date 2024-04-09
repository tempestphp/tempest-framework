<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\ConsoleArgumentDefinition;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;

final class InvalidCommandException extends ConsoleException
{
    public function __construct(
        private readonly ConsoleCommand $consoleCommand,
        /** @var \Tempest\Console\ConsoleArgumentDefinition[] $invalidDefinitions */
        private readonly array $invalidDefinitions,
    ) {
    }

    public function render(ConsoleOutput $output): void
    {
        $output->error("Invalid command usage:");

        (new RenderConsoleCommand($output))($this->consoleCommand);

        $missingArguments = implode(', ', array_map(
            fn (ConsoleArgumentDefinition $argumentDefinition) => $argumentDefinition->name,
            $this->invalidDefinitions,
        ));

        if ($missingArguments) {
            $output->writeln("Missing arguments: {$missingArguments}");
        }
    }
}
