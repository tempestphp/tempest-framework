<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\CommandBus\Fixtures;

use Tempest\Commands\CommandBusMiddleware;

class MyCommandBusMiddleware implements CommandBusMiddleware
{
    public static bool $hit = false;

    public function __invoke(object $command, callable $next): void
    {
        self::$hit = true;

        $next($command);
    }
}
