<?php

use App\Controllers\HomeController;
use App\Controllers\TestController;
use Tempest\Route\RouteConfig;

return new RouteConfig(
    controllers: [
        TestController::class,
        HomeController::class,
    ],
);