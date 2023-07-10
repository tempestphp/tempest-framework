<?php

use App\Controllers\TestController;
use App\Modules\Home\HomeController;
use App\Modules\Posts\PostController;
use Tempest\Route\RouteConfig;

return new RouteConfig(
    controllers: [
        TestController::class,
        HomeController::class,
        PostController::class,
    ],
);