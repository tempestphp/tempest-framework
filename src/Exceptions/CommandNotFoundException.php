<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\ConsoleOutput;

final class CommandNotFoundException extends ConsoleException
{
    public function __construct(
        private readonly string $commandName
    ) {
    }

    public function render(ConsoleOutput $output): void
    {
        $output->writeln(
            sprintf('Command %s not found', $this->commandName),
        );
    }
}
