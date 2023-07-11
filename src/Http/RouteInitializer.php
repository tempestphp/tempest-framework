<?php

namespace Tempest\Http;

use Tempest\AppConfig;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Initializer;

final readonly class RouteInitializer implements Initializer
{
    public function __construct(
        private Container $container,
        private RouteConfig $routeConfig,
        private AppConfig $appConfig,
    ) {}

    public function initialize(string $className, Container $container): GenericRouter
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