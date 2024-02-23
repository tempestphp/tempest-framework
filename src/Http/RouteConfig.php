<?php

declare(strict_types=1);

namespace Tempest\Http;

use ReflectionMethod;

final class RouteConfig
{
    public function __construct(
        /** @var \Tempest\Http\Route[][] */
        public array $routes = [],
    ) {
    }

    public function addRoute(ReflectionMethod $handler, Route $route): self
    {
        $route->setHandler($handler);

        $this->routes[$route->method->value][] = $route;

        var_dump($this->routes);

        return $this;
    }
}
