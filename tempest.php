<?php

use Tempest\Application\ConsoleApplication;
use Tempest\Application\Kernel;

require_once __DIR__ . '/vendor/autoload.php';

$container = (new Kernel())->init(
    rootDirectory: __DIR__ . '/app',
    rootNamespace: 'App\\',
);

$app = new ConsoleApplication(
    args: $_SERVER['argv'],
    container: $container,
);

$app->run();

exit;