<?php

declare(strict_types=1);

use Tempest\CommandBus\CommandBusConfig;
use Tempest\Core\Middleware;

return new CommandBusConfig(
    middleware: new Middleware(
        // Add your command bus middleware here.
    ),
);
