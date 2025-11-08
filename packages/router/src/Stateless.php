<?php

namespace Tempest\Router;

use Attribute;
use Tempest\Http\Session\VerifyCsrfMiddleware;

/**
 * Mark a route handler as stateless, causing all cookie- and session-related middleware to be skipped.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Stateless implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->without = [
            ...$route->without,
            VerifyCsrfMiddleware::class,
            SetCurrentUrlMiddleware::class,
            SetCookieMiddleware::class,
        ];

        return $route;
    }
}
