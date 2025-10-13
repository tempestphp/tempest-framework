<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Core\Priority;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Random;

#[Priority(Priority::FRAMEWORK)]
final readonly class AsyncCommandMiddleware implements CommandBusMiddleware
{
    public function __construct(
        private CommandRepository $repository,
    ) {}

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $reflector = new ClassReflector($command);

        if ($reflector->hasAttribute(Async::class)) {
            $this->repository->store(Random\uuid(), $command);

            return;
        }

        $next($command);
    }
}
