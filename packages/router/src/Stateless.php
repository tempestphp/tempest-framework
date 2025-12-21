<?php

namespace Tempest\Router;

use Attribute;
use Tempest\Http\Session\ManageSessionLifecycleMiddleware;

/**
 * Mark a route handler as stateless, causing all cookie and session-related middleware to be skipped.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Stateless implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->without = [
            ...$route->without,
            ManageSessionLifecycleMiddleware::class,
            SetCookieHeadersMiddleware::class,
        ];

        return $route;
    }
}
