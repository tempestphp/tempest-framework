<?php

namespace Tempest\Router;

use Attribute;

/**
 * Remove middleware from its associated routes.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class WithoutMiddleware implements RouteDecorator
{
    /** @var class-string<HttpMiddleware>[] */
    private array $withoutMiddleware;

    /** @param class-string<HttpMiddleware> ...$withoutMiddleware */
    public function __construct(string ...$withoutMiddleware)
    {
        $this->withoutMiddleware = $withoutMiddleware;
    }

    public function decorate(Route $route): Route
    {
        $route->without = [
            ...$route->without,
            ...$this->withoutMiddleware,
        ];

        return $route;
    }
}