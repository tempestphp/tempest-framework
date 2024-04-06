<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Exceptions\UnresolvedArgumentsException;

final class ConsoleInputBuilder
{

    public function __construct(
        /** @var ConsoleArgumentDefinition[] */
        protected array $argumentDefinitions,
        protected ConsoleArgumentBag $argumentBag,
    ) {

    }

    /**
     * @return array<ConsoleInputArgument>
     * @throws UnresolvedArgumentsException
     */
    public function build(): array
    {
        $passedArguments = $this->argumentBag->all();
        $definitionArguments = $this->argumentDefinitions;
        $validArguments = [];

        foreach ($passedArguments as $idx => $argument) {
            foreach ($definitionArguments as $definitionIdx => $definitionArgument) {
                if ($this->matchesDefinition($argument, $definitionArgument)) {
                    $validArguments[] = $argument;

                    /**
                     * In case of an input that uses both named and positional arguments,
                     * we'll remove the named argument from the passed arguments array.
                     *
                     * This is to prevent the named argument from being resolved twice.
                     */
                    unset($passedArguments[$idx]);
                    unset($definitionArguments[$definitionIdx]);
                }
            }
        }

        if (count($passedArguments) > 0 || count($definitionArguments) > 0) {
            throw UnresolvedArgumentsException::fromArguments([
                ...$passedArguments,
                ...$definitionArguments,
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

    /**
     * Determines whether the passed argument matches the definition argument.
     *
     * @param ConsoleInputArgument $argument
     * @param ConsoleArgumentDefinition $definitionArgument
     *
     * @return bool
     */
    private function matchesDefinition(ConsoleInputArgument $argument, ConsoleArgumentDefinition $definitionArgument): bool
    {
        if ($argument->position === $definitionArgument->position) {
            return true;
        }

        if (! $argument->name) {
            return false;
        }

        foreach ([$argument->name, ...$definitionArgument->aliases] as $alias) {
            if ($alias === $argument->name) {
                return true;
            }

            return false;
        }
    }

}
