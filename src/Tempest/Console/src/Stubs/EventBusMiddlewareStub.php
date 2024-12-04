<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final class EventBusMiddlewareStub implements EventBusMiddleware
{
    public function __invoke(object $event, EventBusMiddlewareCallable $next): void
    {
        $next($event);
    }
}
