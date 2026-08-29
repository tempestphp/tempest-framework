<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\Router\Route;

/**
 * Adds {@see ThrottleMiddleware} to the decorated route. The middleware is not discovered globally:
 * only routes carrying a throttling attribute get it, and only once.
 */
trait AddsThrottleMiddleware
{
    public function decorate(Route $route): Route
    {
        if (in_array(ThrottleMiddleware::class, $route->middleware, strict: true)) {
            return $route;
        }

        $route->middleware = [
            ...$route->middleware,
            ThrottleMiddleware::class,
        ];

        return $route;
    }
}
