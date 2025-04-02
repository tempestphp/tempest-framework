<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Symfony\Component\Uid\Uuid;
use Tempest\Core\Priority;
use Tempest\Reflection\ClassReflector;

#[Priority(Priority::FRAMEWORK)]
final readonly class AsyncCommandMiddleware implements CommandBusMiddleware
{
    public function __construct(
        private CommandRepository $repository,
    ) {}

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $reflector = new ClassReflector($command);

        if ($reflector->hasAttribute(AsyncCommand::class)) {
            $this->repository->store(Uuid::v7()->toString(), $command);

            return;
        }

        $next($command);
    }
}
