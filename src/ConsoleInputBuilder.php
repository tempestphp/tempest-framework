<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleInputArgument;

final class ConsoleInputBuilder
{
    public function __construct(
        protected ConsoleCommand $command,
        protected ConsoleArgumentBag $argumentBag,
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
            $argument = $this->argumentBag->findFor($argumentDefinition);

            if ($argument === null) {
                $invalidArguments[] = $argumentDefinition;

                continue;
            }

            if ($argumentDefinition->type === 'array' && is_string($argument->value)) {
                $argument = $argument->asArray();
            }

            $validArguments[] = $argument;
        }

        if (count($invalidArguments)) {
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
