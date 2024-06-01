<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\ConsoleConfig;
use Tempest\Console\Input\ConsoleArgumentBag;

final readonly class CompleteConsoleCommandNames
{
    public function __construct(
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function complete(ConsoleArgumentBag $argumentBag, int $current): array
    {
        $currentCommandName = $argumentBag->getCommandName();

        $completions = [];

        foreach ($this->consoleConfig->commands as $name => $definition) {
            if ($definition->hidden) {
                continue;
            }

            if (! str_starts_with($name, $currentCommandName)) {
                continue;
            }

            $completions[] = $name;
        }

        return $completions;
    }
}
