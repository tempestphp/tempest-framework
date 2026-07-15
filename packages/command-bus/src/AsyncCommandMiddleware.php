<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Reflection\ClassReflector;
use Tempest\Support\Priority;
use Tempest\Support\Random;

#[Priority(Priority::FRAMEWORK)]
final readonly class AsyncCommandMiddleware implements CommandBusMiddleware
{
    public function __construct(
        private CommandRepository $repository,
        private CommandBusConfig $commandBusConfig,
    ) {}

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $reflector = new ClassReflector($command);

        if ($reflector->hasAttribute(Async::class) || ($this->commandBusConfig->handlers[$command::class] ?? null)?->handler->hasAttribute(Async::class)) {
            $this->repository->store(Random\uuid(), $command);

            return;
        }

        $next($command);
    }
}
