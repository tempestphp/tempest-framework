<?php

namespace Tempest\Auth\Installer;

use Attribute;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute]
final readonly class Auth implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = AuthMiddleware::class;

        return $route;
    }
}