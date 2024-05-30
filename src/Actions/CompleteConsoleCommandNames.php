<?php

namespace Tempest\Console\Actions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;

final readonly class CompleteConsoleCommandNames
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private ConsoleConfig $consoleConfig,
    ) {}

    public function __invoke(
        ConsoleArgumentBag $argumentBag,
        int $current,
    ): void
    {
        $currentCommandName = $argumentBag->getCommandName();

        foreach ($this->consoleConfig->commands as $name => $definition) {
            if (! str_starts_with($name, $currentCommandName)) {
                continue;
            }

            $this->writeln($name);
        }
    }
}