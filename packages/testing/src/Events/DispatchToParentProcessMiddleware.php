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
            $payload = json_encode([
                'event' => $event::class,
                'data' => $event->serialize(),
            ]);

            $this->writeln('[EVENT] ' . $payload);

            return;
        }

        $next($event);
    }
}
