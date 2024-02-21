<?php

declare(strict_types=1);

namespace Tempest\Events;

interface EventBusMiddleware
{
    public function __invoke(object $event, callable $next): void;
}
