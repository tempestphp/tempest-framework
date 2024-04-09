<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Exceptions\UnresolvedArgumentsException;

final class ConsoleInputBuilder
{
    public function __construct(
        protected ConsoleCommandDefinition $commandDefinition,
        protected ConsoleArgumentBag $argumentBag,
    ) {}

    /**
     * @return array<ConsoleInputArgument>
     * @throws UnresolvedArgumentsException
     */
    public function build(): array
    {
        $validArguments = [];
        $invalidDefinitions = [];

        $argumentDefinitions = $this->commandDefinition->argumentDefinitions;

        foreach ($argumentDefinitions as $argumentDefinition) {
            $value = $this->argumentBag->findFor($argumentDefinition);

            if ($value === null) {
                $invalidDefinitions[] = $argumentDefinition;

                continue;
            }

            $validArguments[] = $value;
        }

        if (count($invalidDefinitions)) {
            throw new UnresolvedArgumentsException($invalidDefinitions);
        }

        return array_map(
            callback: fn (ConsoleInputArgument $argument) => $argument->value,
            array: $validArguments,
        );
    }
}
