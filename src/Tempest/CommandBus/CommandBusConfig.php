<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Support\Reflection\MethodReflector;

final class CommandBusConfig
{
    public function __construct(
        /** @var \Tempest\CommandBus\CommandHandler[] */
        public array $handlers = [],

        /** @var \Tempest\CommandBus\CommandBusMiddleware[] */
        public array $middleware = [],
    ) {
    }

    public function addHandler(CommandHandler $commandHandler, string $commandName, MethodReflector $handler): self
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
