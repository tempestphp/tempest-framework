<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Exceptions\UnresolvedArgumentsException;

final class ConsoleInputBuilder
{
    public function __construct(
        protected ConsoleCommandDefinition $commandDefinition,
        protected ConsoleArgumentBag $argumentBag,
    ) {

    }

    /**
     * @return array<ConsoleInputArgument>
     * @throws UnresolvedArgumentsException
     */
    public function build(): array
    {
        $validArguments = [];

        $passedArguments = $this->argumentBag->all();
        $argumentDefinitionList = $this->commandDefinition->argumentDefinitions;

        if (! $argumentDefinitionList && $passedArguments) {
            throw UnresolvedArgumentsException::fromArguments($passedArguments);
        }

        foreach ($argumentDefinitionList as $definitionKey => $definitionArgument) {
            $validArguments[] = $this->resolveArgument($definitionArgument, $passedArguments);
            unset($argumentDefinitionList[$definitionKey]);
        }

        if (count($passedArguments) > 0 || count($argumentDefinitionList) > 0) {
            throw UnresolvedArgumentsException::fromArguments([
                ...$passedArguments,
                ...$argumentDefinitionList,
            ]);
        }

        return $this->toValues($validArguments);
    }

    /**
     * @param ConsoleInputArgument[] $validArguments
     *
     * @return array
     */
    private function toValues(array $validArguments): array
    {
        return array_map(
            callback: fn (ConsoleInputArgument $argument) => $argument->value,
            array: $validArguments
        );
    }

    private function resolveArgument(ConsoleArgumentDefinition $argumentDefinition, array &$passedArguments): ?ConsoleInputArgument
    {
        foreach ($passedArguments as $key => $argument) {
            if ($argumentDefinition->matchesArgument($argument)) {
                unset($passedArguments[$key]);

                return $argument;
            }
        }

        /**
         * In case there was no passed argument that matches this definition argument,
         * we'll check if the definition argument has a default value.
         */
        if (! $argumentDefinition->hasDefault) {
            return null;
        }

        return new ConsoleInputArgument(
            name: $argumentDefinition->name,
            value: $argumentDefinition->default,
            position: $argumentDefinition->position,
        );
    }
}
