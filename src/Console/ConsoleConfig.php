<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionMethod;

final class ConsoleConfig
{
    public function __construct(
        /** @var \ReflectionMethod[] $handlers */
        public array $handlers = [],
    ) {
    }

    public function addCommand(ReflectionMethod $handler): self
    {
        $commandName = strtolower($handler->getDeclaringClass()->getShortName() . ':' . $handler->getName());

        $this->handlers[$commandName] = $handler;

        return $this;
    }
}
