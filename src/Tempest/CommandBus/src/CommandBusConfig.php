<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\CommandBus\AsyncCommandRepositories\FileCommandRepository;
use Tempest\Reflection\MethodReflector;

final class CommandBusConfig
{
    public function __construct(
        /** @var \Tempest\CommandBus\CommandHandler[] */
        public array $handlers = [],

        /** @var array<array-key, class-string<\Tempest\CommandBus\CommandBusMiddleware>> */
        public array $middleware = [],

        /** @var class-string<\Tempest\CommandBus\CommandRepository> $commandRepositoryClass */
        public string $commandRepositoryClass = FileCommandRepository::class,
    ) {}

    /**
     * @throws CommandHandlerAlreadyExists
     */
    public function addHandler(CommandHandler $commandHandler, string $commandName, MethodReflector $handler): self
    {
        if (array_key_exists($commandName, $this->handlers)) {
            throw new CommandHandlerAlreadyExists($commandName, new: $handler, existing: $this->handlers[$commandName]->handler);
        }

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
