<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Throwable;

use function Tempest\Support\arr;

if (class_exists(\Tempest\Console\ConsoleCommand::class)) {
    final readonly class HandleAsyncCommand
    {
        use HasConsole;

        public function __construct(
            private CommandBusConfig $commandBusConfig,
            private Container $container,
            private CommandRepository $repository,
        ) {}

        #[ConsoleCommand(name: 'command:handle', description: 'Manually executes a pending command')]
        public function __invoke(?string $uuid = null): ExitCode
        {
            try {
                if ($uuid) {
                    $command = $this->repository->findPendingCommand($uuid);
                } else {
                    $pendingCommands = arr($this->repository->getPendingCommands());

                    if ($pendingCommands->isEmpty()) {
                        $this->error('No pending command found.');

                        return ExitCode::ERROR;
                    }

                    $uuid = $pendingCommands->keys()->first();
                    $command = $pendingCommands->get($uuid);
                }

                if (! $command) {
                    $this->error('No pending command found.');

                    return ExitCode::ERROR;
                }

                $time = new DateTimeImmutable();
                $this->keyValue(
                    key: $uuid,
                    value: "<style='fg-gray'>{$time->format('Y-m-d H:i:s')}</style>",
                );

                $commandHandler = $this->commandBusConfig->handlers[$command::class] ?? null;

                if (! $commandHandler) {
                    $commandClass = $command::class;
                    $this->error("No handler found for command {$commandClass}.");

                    return ExitCode::ERROR;
                }

                $commandHandler->handler->invokeArgs(
                    $this->container->get($commandHandler->handler->getDeclaringClass()->getName()),
                    [$command],
                );

                $this->repository->markAsDone($uuid);

                $this->keyValue(
                    key: "<style='fg-gray'>{$uuid}</style>",
                    value: "<style='fg-green bold'>SUCCESS</style>",
                );

                return ExitCode::SUCCESS;
            } catch (Throwable $throwable) {
                $this->repository->markAsFailed($uuid);
                $this->error($throwable->getMessage());

                return ExitCode::ERROR;
            }
        }
    }
}
