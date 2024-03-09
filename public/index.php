<?php

use Tempest\Container\GenericContainer;
use Tempest\Tempest;

require_once __DIR__ . '/../vendor/autoload.php';

$container = GenericContainer::getInstance();

Tempest::http(container: $container, basePath: __DIR__ . '/../')->run();

exit;
