<?php

namespace Tempest\Route;

use Tempest\AppConfig;
use Tempest\Container\Container;

final readonly class RouteInitializer
{
    public function __construct(
        private Container $container,
        private RouteConfig $routeConfig,
        private AppConfig $appConfig,
    ) {}

    public function __invoke(): Router
    {
        $router = new Router(
            container: $this->container,
            appConfig: $this->appConfig,
        );

        foreach ($this->routeConfig->controllers as $controller) {
            $router->registerController($controller);
        }

        return $router;
    }
}