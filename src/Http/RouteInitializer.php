<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\AppConfig;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Initializer;
use Tempest\Interfaces\Router;

final readonly class RouteInitializer implements Initializer
{
    public function __construct(
        private Container $container,
        private RouteConfig $routeConfig,
        private AppConfig $appConfig,
    ) {
    }

    public function initialize(string $className, Container $container): GenericRouter
    {
        $router = new GenericRouter(
            container: $this->container,
            appConfig: $this->appConfig,
        );

        foreach ($this->routeConfig->controllers as $controller) {
            $router->registerController($controller);
        }

        $this->container->singleton(Router::class, fn () => $router);

        return $router;
    }
}
