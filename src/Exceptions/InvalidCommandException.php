<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentDefinition;

final class InvalidCommandException extends ConsoleException
{
    public function __construct(
        private readonly ConsoleCommand $consoleCommand,
        /** @var \Tempest\Console\Input\ConsoleArgumentDefinition[] $invalidDefinitions */
        private readonly array $invalidDefinitions,
    ) {
    }

    public function render(Console $console): void
    {
        $console->error("Invalid command usage:");

        (new RenderConsoleCommand($console))($this->consoleCommand);

        $missingArguments = implode(', ', array_map(
            fn (ConsoleArgumentDefinition $argumentDefinition) => $argumentDefinition->name,
            $this->invalidDefinitions,
        ));

        if ($missingArguments) {
            $console->writeln("Missing arguments: {$missingArguments}");
        }
    }
}
