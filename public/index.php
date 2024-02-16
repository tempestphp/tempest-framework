<?php

use App\AppPackage;
use Tempest\AppConfig;
use Tempest\Application\Environment;
use Tempest\Tempest;
use Tempest\TempestPackage;
use function Tempest\env;

require_once __DIR__ . '/../vendor/autoload.php';

Tempest::setupEnv(__DIR__ . '/../');

$appConfig = new AppConfig(
    environment: Environment::from(env('ENVIRONMENT')),
    discoveryCache: env('DISCOVERY_CACHE'),
    packages: [
        new TempestPackage(),
        new AppPackage(),
    ],
);

Tempest::boot($appConfig)->http()->run();

exit;