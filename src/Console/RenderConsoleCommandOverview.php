<?php

declare(strict_types=1);

namespace Tempest\Console;

final readonly class RenderConsoleCommandOverview
{
    public function __invoke(ConsoleConfig $config): string
    {
        $lines = [
            ConsoleStyle::BOLD(ConsoleStyle::BG_DARK_BLUE(" Tempest Console ")),
            '',
        ];

        /** @var \Tempest\Console\ConsoleCommand[][] $commands */
        $commands = [];

        foreach ($config->commands as $consoleCommand) {
            $parts = explode(':', $consoleCommand->getName());

            $group = count($parts) > 1 ? $parts[0] : 'General';

            $commands[$group][$consoleCommand->getName()] = $consoleCommand;
        }

        ksort($commands);

        foreach ($commands as $group => $commandsForGroup) {
            $lines[] = ConsoleStyle::BOLD(ConsoleStyle::BG_BLUE(' ' . ucfirst($group) . ' '));

            foreach ($commandsForGroup as $consoleCommand) {
                $renderedConsoleCommand = (new RenderConsoleCommand())($consoleCommand);
                $lines[] = "  {$renderedConsoleCommand}";
            }

            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }
}
