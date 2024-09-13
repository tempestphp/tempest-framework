<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Fixtures;

use Tempest\EventBus\EventBusMiddleware;

final class MyEventBusMiddleware implements EventBusMiddleware
{
    public static bool $hit = false;

    public function __invoke(object $event, callable $next): void
    {
        self::$hit = true;

        $next($event);
    }
}
