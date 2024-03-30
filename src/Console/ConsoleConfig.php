<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionMethod;

final class ConsoleConfig
{
    public function __construct(
        /** @var \Tempest\Console\ConsoleCommand[] $commands */
        public array $commands = [],
    ) {
    }

    public function addCommand(ReflectionMethod $handler, ConsoleCommand $consoleCommand): self
    {
        $consoleCommand->setHandler($handler);

        $this->commands[$consoleCommand->getName()] = $consoleCommand;

        foreach ($consoleCommand->getAliases() as $aliasName) {
            if (isset($this->commands[$aliasName])) {
                continue;
            }

            $alias = new ConsoleCommand(
                name: $aliasName,
                description: $consoleCommand->getDescription(),
                aliases: [],
                isDangerous: $consoleCommand->isDangerous(),
                isHidden: true,
            );

            $alias->setHandler($handler);

            $this->commands[$aliasName] = $alias;
        }

        return $this;
    }
}
