<?php

declare(strict_types=1);

use Tempest\Discovery\DiscoveryLocation;
use Tempest\Router\HttpApplication;
use Tempest\Router\WorkerApplication;

require_once __DIR__ . '/../vendor/autoload.php';

if (function_exists('frankenphp_handle_request')) {
    WorkerApplication::boot(
        root: __DIR__ . '/../',
        discoveryLocations: [
            new DiscoveryLocation('Tests\\Tempest\\Fixtures\\', __DIR__ . '/../tests/Fixtures/'),
        ],
    )->run();

    exit();
}

HttpApplication::boot(__DIR__ . '/../', discoveryLocations: [
    new DiscoveryLocation('Tests\\Tempest\\Fixtures\\', __DIR__ . '/../tests/Fixtures/'),
])->run();

exit();
