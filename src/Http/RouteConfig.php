<?php

declare(strict_types=1);

namespace Tempest\Http;

use ReflectionMethod;

final class RouteConfig
{
    public function __construct(
        /** @var array<string, array<string, \Tempest\Http\Route>> */
        public array $routes = [],
    ) {
    }

    public function addRoute(ReflectionMethod $handler, Route $route): self
    {
        $route->setHandler($handler);

        $this->routes[$route->method->value][$route->uri] = $route;

        return $this;
    }
}
