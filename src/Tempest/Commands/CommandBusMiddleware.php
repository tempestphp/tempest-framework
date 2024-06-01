<?php

declare(strict_types=1);

namespace Tempest\Commands;

interface CommandBusMiddleware
{
    public function __invoke(object $command, callable $next): void;
}
