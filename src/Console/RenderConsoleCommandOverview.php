<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\AppConfig;

final readonly class RenderConsoleCommandOverview
{
    public function __construct(
        private AppConfig $appConfig,
        private ConsoleConfig $consoleConfig,
    ) {
    }

    public function __invoke(): string
    {
        $lines = [
            ConsoleStyle::BOLD(ConsoleStyle::BG_DARK_BLUE(" Tempest Console ")),
        ];

        if ($this->appConfig->discoveryCache) {
            $lines[] = ConsoleStyle::BG_RED(' Discovery cache is enabled! ');
        }

        $lines[] = '';

        /** @var \Tempest\Console\ConsoleCommand[][] $commands */
        $commands = [];

        foreach ($this->consoleConfig->commands as $consoleCommand) {
            $parts = explode(':', $consoleCommand->getName());

            $group = count($parts) > 1 ? $parts[0] : 'General';

            $commands[$group][$consoleCommand->getName()] = $consoleCommand;
        }

        ksort($commands);

        foreach ($commands as $group => $commandsForGroup) {
            if (! $commandsForGroup) {
                continue;
            }

            $lines[] = ConsoleStyle::BOLD(ConsoleStyle::BG_BLUE(' ' . ucfirst($group) . ' '));

            foreach ($commandsForGroup as $consoleCommand) {
                if ($consoleCommand->isHidden()) {
                    continue;
                }

                $renderedConsoleCommand = (new RenderConsoleCommand())($consoleCommand);
                $lines[] = "  {$renderedConsoleCommand}";
            }

            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }
}
