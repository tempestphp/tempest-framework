<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface EventBusMiddleware
{
    public function __invoke(object $event, callable $next): void;
}
