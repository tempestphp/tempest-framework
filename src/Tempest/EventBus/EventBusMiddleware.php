<?php

declare(strict_types=1);

namespace Tempest\EventBus;

interface EventBusMiddleware
{
    public function __invoke(object $event, callable $next): void;
}
