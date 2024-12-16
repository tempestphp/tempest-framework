<?php

declare(strict_types=1);

use Tempest\Console\Middleware\ResolveOrRescueMiddleware;
use Tempest\Console\Middleware\OverviewMiddleware;
use Tempest\Console\Middleware\InvalidCommandMiddleware;
use Tempest\Console\Middleware\HelpMiddleware;
use Tempest\Console\Middleware\ConsoleExceptionMiddleware;
use Tempest\Console\ConsoleConfig;

return new ConsoleConfig(
    name: 'Console Name',
    middleware: [
        OverviewMiddleware::class,
        ConsoleExceptionMiddleware::class,
        ResolveOrRescueMiddleware::class,
        InvalidCommandMiddleware::class,
        HelpMiddleware::class,
    ]
);