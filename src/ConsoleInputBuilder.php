<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Exceptions\InvalidCommandException;

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
        $invalidDefinitions = [];

        $argumentDefinitions = $this->command->getArgumentDefinitions();

        foreach ($argumentDefinitions as $argumentDefinition) {
            $value = $this->argumentBag->findFor($argumentDefinition);

            if ($value === null) {
                $invalidDefinitions[] = $argumentDefinition;

                continue;
            }

            $validArguments[] = $value;
        }

        if (count($invalidDefinitions)) {
            throw new InvalidCommandException(
                $this->command,
                $invalidDefinitions
            );
        }

        return array_map(
            callback: fn (ConsoleInputArgument $argument) => $argument->value,
            array: $validArguments,
        );
    }
}
