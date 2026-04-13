<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\CommandBus\CommandBusMiddleware;
use Tempest\CommandBus\CommandBusMiddlewareCallable;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Support\Priority;

#[SkipDiscovery]
#[Priority(Priority::NORMAL)]
final class CommandBusMiddlewareStub implements CommandBusMiddleware
{
    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $next($command);
    }
}
