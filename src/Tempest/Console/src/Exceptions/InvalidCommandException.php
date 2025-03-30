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
        public readonly ConsoleCommand $consoleCommand,

        /** @var \Tempest\Console\Input\ConsoleArgumentDefinition[] $invalidArguments */
        public readonly array $invalidArguments,
    ) {}

    public function render(Console $console): void
    {
        $missingArguments = implode(', ', array_map(
            fn (ConsoleArgumentDefinition $argumentDefinition) => $argumentDefinition->name,
            $this->invalidArguments,
        ));

        if ($missingArguments) {
            $console->writeln();
            $console->error("Missing arguments: {$missingArguments}");
        } else {
            $console->writeln();
            $console->error('Invalid command usage.');
        }

        $console->info('Run again with --help for more information.');
    }
}
