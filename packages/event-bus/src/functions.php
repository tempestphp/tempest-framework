<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;
use Tempest\Container;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;

/**
 * Dispatches the given `$event`, triggering all associated event listeners.
 */
function event(string|object $event): void
{
    $eventBus = Container\get(EventBus::class);

    $eventBus->dispatch($event);
}

/**
 * Registers a closure-based event listener for the given `$event`.
 */
function listen(Closure $handler, ?string $event = null): void
{
    $config = Container\get(EventBusConfig::class);

    $config->addClosureHandler($handler, $event);
}
