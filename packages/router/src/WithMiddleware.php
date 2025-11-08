<?php

namespace Tempest\Router;

use Attribute;


/**
 * Add middleware to its associated routes.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class WithMiddleware implements RouteDecorator
{
    /** @var class-string<HttpMiddleware>[] */
    private array $middleware;

    /** @param class-string<HttpMiddleware> ...$middleware */
    public function __construct(string ...$middleware)
    {
        $this->middleware = $middleware;
    }

    public function decorate(Route $route): Route
    {
        $route->middleware = [
            ...$route->middleware,
            ...$this->middleware,
        ];

        return $route;
    }
}