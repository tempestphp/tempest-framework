<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\CompletesConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentBag;

final readonly class CompleteConsoleCommandArguments implements CompletesConsoleCommand
{
    public function complete(
        ConsoleCommand $command,
        ConsoleArgumentBag $argumentBag,
        int $current,
    ): array {
        $definitions = $command->getArgumentDefinitions();

        $last = $argumentBag->last();

        if ($last && $last->value === null) {
            return [];
        }

        $completions = [];

        foreach ($definitions as $definition) {
            if ($definition->type !== 'array' && $argumentBag->has($definition->name)) {
                continue;
            }

            $argument = "--{$definition->name}";

            if ($definition->type !== 'bool') {
                $argument .= '=';
            }

            $completions[] = $argument;
        }

        return $completions;
    }
}
