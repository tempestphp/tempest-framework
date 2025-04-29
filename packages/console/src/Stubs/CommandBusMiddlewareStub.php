<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\CommandBus\CommandBusMiddleware;
use Tempest\CommandBus\CommandBusMiddlewareCallable;
use Tempest\Core\Priority;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
#[Priority(Priority::NORMAL)]
final class CommandBusMiddlewareStub implements CommandBusMiddleware
{
    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $next($command);
    }
}
