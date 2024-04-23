<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;

final readonly class RenderConsoleCommandHelp
{
    public function __construct(private Console $console)
    {
    }

    public function __invoke(ConsoleCommand $consoleCommand): void
    {
        $this->console
            ->when($consoleCommand->help, fn (ConsoleOutput $output) => $output->writeln("<comment>{$consoleCommand->help}</comment>"))
            ->write('<h2>Usage</h2>');

        (new RenderConsoleCommand($this->console))($consoleCommand);

        foreach ($consoleCommand->getArgumentDefinitions() as $argumentDefinition) {
            $this->console
                ->writeln()
                ->when($argumentDefinition->help, fn (ConsoleOutput $output) => $output->writeln('<comment>' . $argumentDefinition->help . '</comment>'))
                ->write("<em>{$argumentDefinition->name}</em>")
                ->when($argumentDefinition->aliases !== [], fn (ConsoleOutput $output) => $output->write(' (' . implode(', ', $argumentDefinition->aliases) . ')'))
                ->when($argumentDefinition->description, fn (ConsoleOutput $output) => $output->write(' — ' . $argumentDefinition->description))
            ;
        }

        $this->console->writeln();
    }
}
