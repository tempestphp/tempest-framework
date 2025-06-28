<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\CommandBus\AsyncCommandRepositories\FileCommandRepository;
use Tempest\Core\Middleware;
use Tempest\Reflection\MethodReflector;

final class CommandBusConfig
{
    public function __construct(
        /** @var \Tempest\CommandBus\CommandHandler[] */
        public array $handlers = [],

        /** @var Middleware<\Tempest\CommandBus\CommandBusMiddleware> */
        public Middleware $middleware = new Middleware(),

        /** @var class-string<\Tempest\CommandBus\CommandRepository> $commandRepositoryClass */
        public string $commandRepositoryClass = FileCommandRepository::class,
    ) {}

    /**
     * @throws CommandHandlerWasAlreadyRegistered
     */
    public function addHandler(CommandHandler $commandHandler, string $commandName, MethodReflector $handler): self
    {
        if (array_key_exists($commandName, $this->handlers)) {
            throw new CommandHandlerWasAlreadyRegistered($commandName, new: $handler, existing: $this->handlers[$commandName]->handler);
        }

        $this->handlers[$commandName] = $commandHandler
            ->setCommandName($commandName)
            ->setHandler($handler);

        return $this;
    }
}
