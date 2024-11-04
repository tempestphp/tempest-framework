<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Closure;

final readonly class CommandBusMiddlewareCallable
{
    public function __construct(
        private Closure $closure,
    ) {
    }

    public function __invoke(object $command): void
    {
        ($this->closure)($command);
    }
}
