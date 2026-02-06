<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;
use UnitEnum;

interface EventBus
{
    /**
     * Dispatches the given event to all its listeners. The event can be a string, a FQCN or an plain old PHP object.
     */
    public function dispatch(string|object $event): void;

    /**
     * Adds a listener for the given event. The closure accepts the event object as its first parameter, so the `$event` parameter is optional.
     */
    public function listen(Closure $handler, string|UnitEnum|null $event = null): void;
}
