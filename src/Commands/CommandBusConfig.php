<?php

declare(strict_types=1);

namespace Tempest\Commands;

use ReflectionMethod;

final class CommandBusConfig
{
    public function __construct(
        /** @var \Tempest\Commands\CommandHandler[] */
        public array $handlers = [],

        /** @var \Tempest\Commands\CommandBusMiddleware[] */
        public array $middleware = [],
    ) {
    }

    public function addHandler(CommandHandler $commandHandler, string $commandName, ReflectionMethod $handler): self
    {
        $this->handlers[$commandName] = $commandHandler
            ->setCommandName($commandName)
            ->setHandler($handler);

        return $this;
    }

    public function addMiddleware(CommandBusMiddleware $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }
}
