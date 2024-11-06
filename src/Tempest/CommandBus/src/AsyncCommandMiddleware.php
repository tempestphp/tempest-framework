<?php

namespace Tempest\CommandBus;

use Ramsey\Uuid\Uuid;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Reflection\ClassReflector;

final readonly class AsyncCommandMiddleware implements CommandBusMiddleware
{
    public function __construct(
        private CommandBusConfig $commandBusConfig,
        private AsyncCommandRepository $repository,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function onBooted(): void
    {
        $this->commandBusConfig->addMiddleware(self::class);
    }

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $reflector = new ClassReflector($command);

        if ($reflector->hasAttribute(AsyncCommand::class))
        {
            $this->repository->store(Uuid::uuid7()->toString(), $command);
            return;
        }

        $next($command);
    }
}