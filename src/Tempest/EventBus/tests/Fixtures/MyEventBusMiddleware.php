<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Fixtures;

use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final class MyEventBusMiddleware implements EventBusMiddleware
{
    public static int $hits = 0;

    public function __invoke(object $event, EventBusMiddlewareCallable $next): void
    {
        self::$hits += 1;

        $next($event);
    }
}
