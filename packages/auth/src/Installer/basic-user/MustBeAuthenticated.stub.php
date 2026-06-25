<?php

namespace Tempest\Auth\Installer;

use Attribute;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute]
final readonly class MustBeAuthenticated implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = MustBeAuthenticatedMiddleware::class;

        return $route;
    }
}