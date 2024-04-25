<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionMethod;

final class ConsoleConfig
{
    public function __construct(
        public string $name = 'Tempest',

        /** @var \Tempest\Console\ConsoleCommand[] $commands */
        public array $commands = [],

        /** @var \Tempest\Console\ConsoleCommand[] $commands */
        public array $scheduledCommands = [],
    ) {
    }

    public function addCommand(ReflectionMethod $handler, ConsoleCommand $consoleCommand): self
    {
        $consoleCommand->setHandler($handler);

        $this->commands[$consoleCommand->getName()] = $consoleCommand;

        if ($consoleCommand->cron !== null) {
            $this->scheduledCommands[] = $consoleCommand;

            usort($this->scheduledCommands, function (ConsoleCommand $a, ConsoleCommand $b) {
                return $a->cron->runInBackground <=> $b->cron->runInBackground;
            });
        }

        foreach ($consoleCommand->aliases as $alias) {
            if (array_key_exists($alias, $this->commands)) {
                continue;
            }

            $this->commands[$alias] = $consoleCommand;
        }

        return $this;
    }
}
