<?php

namespace Tests\Tempest\Fixtures\Events;

use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final class DiscoveredEventBusMiddleware implements EventBusMiddleware
{
    public static bool $hit = false;

    public function __invoke(string|object $event, EventBusMiddlewareCallable $next): void
    {
        self::$hit = true;

        $next($event);
    }
}
