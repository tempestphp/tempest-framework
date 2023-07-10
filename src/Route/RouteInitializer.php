<?php

namespace Tempest\Route;

use Tempest\AppConfig;
use Tempest\Interfaces\Container;

final readonly class RouteInitializer
{
    public function __construct(
        private Container $container,
        private RouteConfig $routeConfig,
        private AppConfig $appConfig,
    ) {}

    public function __invoke(): GenericRouter
    {
        $router = new GenericRouter(
            container: $this->container,
            appConfig: $this->appConfig,
        );

        foreach ($this->routeConfig->controllers as $controller) {
            $router->registerController($controller);
        }

        return $router;
    }
}