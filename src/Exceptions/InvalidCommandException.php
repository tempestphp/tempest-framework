<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;

final class InvalidCommandException extends ConsoleException
{
    public function __construct(
        private readonly string $initialCommand,
        private readonly ConsoleCommand $consoleCommand,
    ) {
    }

    public function render(ConsoleOutput $output): void
    {
        $output->error("Invalid command: {$this->initialCommand}");

        (new RenderConsoleCommand($output))($this->consoleCommand);
    }
}
