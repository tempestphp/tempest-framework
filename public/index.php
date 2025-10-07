<?php

use Tempest\Discovery\DiscoveryLocation;
use Tempest\Router\HttpApplication;
use Tempest\Router\WorkerApplication;

require_once __DIR__ . '/../vendor/autoload.php';

$discoveryLocations = [
    new DiscoveryLocation('Tests\\Tempest\\Fixtures\\', __DIR__ . '/../tests/Fixtures/'),
];

if (function_exists('frankenphp_handle_request')) {
    ignore_user_abort(true);

    $application = WorkerApplication::boot(__DIR__ . '/../', discoveryLocations: $discoveryLocations);

    $handler = static function () use ($application) {
        $application->run();
    };

    $maxRequests = (int)($_SERVER['MAX_REQUESTS'] ?? 0);

    for ($nbRequests = 0; ! $maxRequests || $nbRequests < $maxRequests; ++$nbRequests) {
        $keepRunning = frankenphp_handle_request($handler);

        gc_collect_cycles();

        if (! $keepRunning) break;
    }
} else {
    HttpApplication::boot(__DIR__ . '/../', discoveryLocations: $discoveryLocations)->run();

    exit();

}

