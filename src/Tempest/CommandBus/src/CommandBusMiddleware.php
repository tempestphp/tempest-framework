<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

interface CommandBusMiddleware
{
    /** @param callable(object $command): void $next */
    public function __invoke(object $command, callable $next): void;
}
