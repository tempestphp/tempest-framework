<?php

declare(strict_types=1);

namespace Tempest\Commands;

use Closure;
use Tempest\Interface\CommandBus;
use Tempest\Interface\Container;

final class GenericCommandBus implements CommandBus
{
    /** @var object[] */
    private array $history = [];

    public function __construct(
        private readonly Container $container,
        private readonly CommandBusConfig $commandBusConfig,
    ) {
    }

    public function dispatch(object $command): void
    {
        $callable = $this->getCallable();

        $callable($command);
    }

    private function getCallable(): Closure
    {
        $callable = function (object $command) {
            $handler = $this->getCommandHandler($command)->handler;

            if (! $handler) {
                throw new CommandHandlerNotFound($command);
            }

            $handler->invoke(
                $this->container->get($handler->getDeclaringClass()->getName()),
                $command,
            );

            $this->history[] = $command;
        };

        $middlewareStack = $this->commandBusConfig->middleware;

        while ($middleware = array_pop($middlewareStack)) {
            $callable = fn (object $command) => $this->container->get($middleware::class)($command, $callable);
        }

        return $callable;
    }

    private function getCommandHandler(object $command): ?CommandHandler
    {
        return $this->commandBusConfig->handlers[$command::class] ?? null;
    }

    public function getHistory(): array
    {
        return $this->history;
    }
}
