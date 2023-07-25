<?php

use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

$container = (new Kernel)->init(
    rootDirectory: __DIR__ . '/../app',
    rootNamespace: 'App\\',
);

$app = new HttpApplication($container);

$app->run();

exit;