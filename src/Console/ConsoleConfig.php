<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionMethod;

final class ConsoleConfig
{
    public function __construct(
        /** @var ConsoleCommand[] $commands */
        public array $commands = [],
    ) {
    }

    public function addCommand(ReflectionMethod $handler, ConsoleCommand $consoleCommand): self
    {
        $consoleCommand->setHandler($handler);

        $this->commands[$consoleCommand->getName()] = $consoleCommand;

        foreach ($consoleCommand->getAliases() as $alias) {
            if (isset($this->commands[$alias])) {
                continue;
            }

            $this->commands[$alias] = $consoleCommand;
        }

        return $this;
    }
}
