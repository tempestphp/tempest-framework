<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;

final readonly class HandleAsyncCommand
{
    use HasConsole;

    public function __construct(
        private CommandBusConfig $commandBusConfig,
        private Container $container,
        private Console $console,
        private AsyncCommandRepository $repository,
    ) {
    }

    #[ConsoleCommand(name: 'command:handle')]
    public function __invoke(string $uuid): void
    {
        $command = $this->repository->find($uuid);

        $commandHandler = $this->commandBusConfig->handlers[$command::class] ?? null;

        if (! $commandHandler) {
            $commandClass = $command::class;

            $this->error("No handler found for command {$commandClass}");

            return;
        }

        $commandHandler->handler->invokeArgs(
            $this->container->get($commandHandler->handler->getDeclaringClass()->getName()),
            [$command],
        );

        $this->success('Done');
    }
}
