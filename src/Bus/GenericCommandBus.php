<?php

namespace Tempest\Bus;

use Closure;
use ReflectionMethod;
use Tempest\Interface\CommandBus;
use Tempest\Interface\Container;

final class GenericCommandBus implements CommandBus
{
    /** @var object[] */
    private array $history = [];

    /** @var ReflectionMethod[] */
    private array $handlers = [];

    /** @var \Tempest\Bus\Middleware[] */
    private array $middleware = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    public function dispatch(object $command): void
    {
        $callable = $this->getCallable();

        $callable($command);
    }

    private function getCallable(): Closure
    {
        $callable = function (object $command) {
            $handler = $this->getHandler($command);

            if (! $handler) {
                throw new CommandHandlerNotFound($command);
            }

            $handler->invoke(
                $this->container->get($handler->getDeclaringClass()->getName()),
                $command,
            );

            $this->history[] = $command;
        };

        $middlewareStack = $this->middleware;

        while ($middleware = array_pop($middlewareStack)) {
            $callable = fn (object $command) => $this->container->get($middleware::class)($command, $callable);
        }

        return $callable;
    }

    private function getHandler(object $command): ?ReflectionMethod
    {
        return $this->handlers[$command::class] ?? null;
    }

    public function addMiddleware(string $middleware): self
    {
        $this->middleware[] = $middleware;
    }

    public function addHandler(string $commandName, ReflectionMethod $handler): self
    {
        $this->handlers[$commandName] = $handler;

        return $this;
    }

    public function getHistory(): array
    {
        return $this->history;
    }
}
