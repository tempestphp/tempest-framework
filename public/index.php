<?php

use Tempest\AppConfig;
use Tempest\Application\Environment;
use Tempest\Tempest;

require_once __DIR__ . '/../vendor/autoload.php';

Tempest::setupEnv(__DIR__ . '/../');

Tempest::boot(new AppConfig(
    appPath: __DIR__ . '/../app',
    appNamespace: 'App\\',
    environment: Environment::from(getenv('ENVIRONMENT')),
    discoveryCache: getenv('DISCOVERY_CACHE'),
))->http()->run();

exit;