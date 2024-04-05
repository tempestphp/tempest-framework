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
    ) {
    }

    public function addCommand(ReflectionMethod $handler, ConsoleCommand $consoleCommand): self
    {
        $consoleCommand->setHandler($handler);

        $this->commands[$consoleCommand->getName()] = $consoleCommand;

        return $this;
    }
}
