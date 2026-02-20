<?php

declare(strict_types=1);

use Tempest\Router\HttpApplication;
use Tempest\Router\WorkerApplication;

require_once __DIR__ . '/../vendor/autoload.php';

if (function_exists('frankenphp_handle_request')) {
    WorkerApplication::boot(
        root: __DIR__ . '/../',
        maxLoops: 500, // TODO: make it configurable
    )->run();

    exit();
}

HttpApplication::boot(__DIR__ . '/../')->run();
