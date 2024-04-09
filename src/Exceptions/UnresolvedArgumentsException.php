<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\ConsoleOutput;

final class UnresolvedArgumentsException extends ConsoleException
{
    public function __construct(
        /** @var \Tempest\Console\ConsoleArgumentDefinition[] $invalidDefinitions */
        private array $invalidDefinitions,
    ) {
    }

    public function render(ConsoleOutput $output): void
    {
        $output->error('Invalid command');

        foreach ($this->invalidDefinitions as $definition) {
            $output->writeln(
                sprintf(
                    'Argument %s is missing',
                    $definition->name,
                ),
            );
        }
    }
}
