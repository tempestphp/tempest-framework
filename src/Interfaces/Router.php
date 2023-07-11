<?php

namespace Tempest\Interfaces;

use Tempest\Http\Response;
use Tempest\Http\Route;

interface Router
{
    public function registerRoute(Route $route, string $controllerClass, string $controllerMethod): self;

    public function dispatch(Request $request): Response;

    public function toUri(string $controller, ?string $method = null, ...$params): ?string;
}
