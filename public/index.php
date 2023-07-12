<?php

use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

(new Kernel)
    ->init(__DIR__ . '/../app')
    ->get(HttpApplication::class)
    ->run();

exit;