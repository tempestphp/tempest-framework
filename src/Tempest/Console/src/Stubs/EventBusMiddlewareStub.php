<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Core\Priority;
use Tempest\Discovery\DoNotDiscover;
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

#[DoNotDiscover]
#[Priority(Priority::NORMAL)]
final class EventBusMiddlewareStub implements EventBusMiddleware
{
    public function __invoke(string|object $event, EventBusMiddlewareCallable $next): void
    {
        $next($event);
    }
}
