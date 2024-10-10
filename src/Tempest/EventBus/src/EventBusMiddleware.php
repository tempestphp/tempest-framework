<?php

declare(strict_types=1);

namespace Tempest\EventBus;

interface EventBusMiddleware
{
    /** @param callable(object $event): void $next */
    public function __invoke(object $event, callable $next): void;
}
