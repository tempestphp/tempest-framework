<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

interface CommandBusMiddleware
{
    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void;
}
