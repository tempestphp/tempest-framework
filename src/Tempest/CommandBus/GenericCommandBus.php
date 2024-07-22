<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Closure;
use Tempest\Container\Container;

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
        $commandHandler = $this->getCommandHandler($command);

        if (! $commandHandler) {
            throw new CommandHandlerNotFound($command);
        }

        $callable = $this->getCallable($commandHandler);

        $callable($command);
    }

    private function getCallable(CommandHandler $commandHandler): Closure
    {
        $callable = function (object $command) use ($commandHandler) {
            $commandHandler->handler->invoke(
                $this->container->get($commandHandler->handler->getDeclaringClass()->getName()),
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
