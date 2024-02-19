<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface CommandBusMiddleware
{
    public function __invoke(object $command, callable $next): void;
}
