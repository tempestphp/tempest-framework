<?php

declare(strict_types=1);

use Tempest\Core\Middleware;
use Tempest\EventBus\EventBusConfig;

return new EventBusConfig(
    middleware: new Middleware(
        // Add your event bus middleware here.
    ),
);
