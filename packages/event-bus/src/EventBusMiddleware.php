<?php

declare(strict_types=1);

namespace Tempest\EventBus;

interface EventBusMiddleware
{
    public function __invoke(string|object $event, EventBusMiddlewareCallable $next): void;
}
