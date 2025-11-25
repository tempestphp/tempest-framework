<?php

namespace Tempest\Testing\Events;

use Tempest\Console\HasConsole;
use Tempest\Discovery\SkipDiscovery;
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

#[SkipDiscovery]
final class DispatchToParentProcessMiddleware implements EventBusMiddleware
{
    use HasConsole;

    public function __invoke(object|string $event, EventBusMiddlewareCallable $next): void
    {
        if ($event instanceof DispatchToParentProcess) {
            $this->writeln('[EVENT] ' . serialize($event));

            return;
        }

        $next($event);
    }
}