<?php

namespace Tempest\Interfaces;

use Tempest\Route\Method;
use Tempest\Route\Response;
use Tempest\Route\Route;

interface Router
{
    public function registerRoute(Route $route, string $controllerClass, string $controllerMethod): self;

    public function dispatch(Method $method, string $uri): Response;

    public function toUri(string $controller, ?string $method = null, ...$params): ?string;
}