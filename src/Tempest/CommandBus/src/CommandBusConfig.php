<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Reflection\MethodReflector;

final class CommandBusConfig
{
    public function __construct(
        /** @var \Tempest\CommandBus\CommandHandler[] */
        public array $handlers = [],

        /** @var array<array-key, class-string<\Tempest\CommandBus\CommandBusMiddleware>> */
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

    /** @param class-string<\Tempest\CommandBus\CommandBusMiddleware> $middlewareClass */
    public function addMiddleware(string $middlewareClass): self
    {
        $this->middleware[] = $middlewareClass;

        return $this;
    }
}
