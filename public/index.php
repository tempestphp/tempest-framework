<?php

use Tempest\Core\AppConfig;
use Tempest\Core\DiscoveryLocation;
use Tempest\Http\HttpApplication;

require_once __DIR__ . '/../vendor/autoload.php';

$root = __DIR__ . '/../';

HttpApplication::boot(
    root: $root,
    appConfig: new AppConfig(
        root: $root,
        discoveryLocations: [
            new DiscoveryLocation('Tests\\Tempest\\Fixtures\\', __DIR__ . '/../tests/Fixtures')
        ],
    ))->run();

exit;
