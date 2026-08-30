<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use Tempest\Console\Input\ConsoleInputArgument;

/** @internal */
final readonly class ResolveConsoleInput
{
    /**
     * @param ConsoleArgumentDefinition[] $argumentDefinitions
     *
     * @return array{0: list<ConsoleInputArgument>, 1: list<ConsoleArgumentDefinition>}
     */
    public function __invoke(ConsoleArgumentBag $argumentBag, array $argumentDefinitions): array
    {
        $validArguments = [];
        $invalidArguments = [];

        foreach ($argumentDefinitions as $argumentDefinition) {
            if ($argumentDefinition->isVariadic) {
                $arguments = $argumentBag->findForVariadicArgument($argumentDefinition);

                if ($arguments === []) {
                    $invalidArguments[] = $argumentDefinition;

                    continue;
                }

                $validArguments = [...$validArguments, ...$arguments];

                continue;
            }

            $argument = $argumentDefinition->type === 'array'
                ? $argumentBag->findArrayFor($argumentDefinition)
                : $argumentBag->findFor($argumentDefinition);

            if (! $argument instanceof ConsoleInputArgument) {
                $invalidArguments[] = $argumentDefinition;

                continue;
            }

            $validArguments[] = $argument;
        }

        return [$validArguments, $invalidArguments];
    }
}
