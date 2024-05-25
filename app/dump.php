<?php

declare(strict_types=1);

require_once  __DIR__ . '/../vendor/autoload.php';

use Tempest\AppConfig;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Kernel;
use Tempest\Log\LogConfig;
use function Tempest\lw;

$appConfig = new AppConfig(
    root: __DIR__ . '/..',
    discoveryCache: true,
    discoveryLocations: [
        new DiscoveryLocation(
            'App\\',
            __DIR__ . '/../app/',
        ),
    ],
);

$kernel = new Kernel($appConfig);
$container = $kernel->init();

$container->config(new LogConfig(
    debugLogPath: __DIR__ . '/debug.log',
));

lw(['hi'], a: ['ho']);
