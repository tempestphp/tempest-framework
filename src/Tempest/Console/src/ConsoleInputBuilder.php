<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleInputArgument;

final readonly class ConsoleInputBuilder
{
    public function __construct(
        private ConsoleCommand $command,
        private ConsoleArgumentBag $argumentBag,
    ) {
    }

    /**
     * @return array<ConsoleInputArgument>
     */
    public function build(): array
    {
        $validArguments = [];
        $invalidArguments = [];

        $argumentDefinitions = $this->command->getArgumentDefinitions();

        foreach ($argumentDefinitions as $argumentDefinition) {
            $argument = $argumentDefinition->type === 'array'
                ? $this->argumentBag->findArrayFor($argumentDefinition)
                : $this->argumentBag->findFor($argumentDefinition);

            if ($argument === null) {
                $invalidArguments[] = $argumentDefinition;

                continue;
            }

            $validArguments[] = $argument;
        }

        if ($invalidArguments !== []) {
            throw new InvalidCommandException(
                $this->command,
                $invalidArguments
            );
        }

        return array_map(
            callback: fn (ConsoleInputArgument $argument) => $argument->value,
            array: $validArguments,
        );
    }
}
