<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus\Fixtures;

use Tempest\CommandBus\CommandBusMiddleware;
use Tempest\CommandBus\CommandBusMiddlewareCallable;

final class MyCommandBusMiddleware implements CommandBusMiddleware
{
    public static bool $hit = false;

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        self::$hit = true;

        $next($command);
    }
}
