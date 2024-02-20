<?php

use Tempest\AppConfig;
use Tempest\Application\Environment;
use Tempest\Tempest;
use function Tempest\env;

require_once __DIR__ . '/../vendor/autoload.php';

$createAppConfig = fn () => new AppConfig(
    environment: Environment::from(env('ENVIRONMENT')),
    discoveryCache: env('DISCOVERY_CACHE'),
);

Tempest::boot(__DIR__ . '/../', $createAppConfig)->http()->run();

exit;