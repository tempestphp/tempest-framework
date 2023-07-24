<?php

declare(strict_types=1);

namespace Tempest\Interface;

use Tempest\Http\Route;

interface Router
{
    public function registerRoute(Route $route, string $controllerClass, string $controllerMethod): self;

    public function dispatch(Request $request): Response;

    public function toUri(array|string $action, ...$params): string;
}
