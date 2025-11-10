<?php

namespace Tempest\Router;

use Attribute;

use function Tempest\Support\path;

/**
 * Add a prefix to the route's URI
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Prefix implements RouteDecorator
{
    public function __construct(
        private string $prefix,
    ) {}

    public function decorate(Route $route): Route
    {
        $route->uri = path($this->prefix, $route->uri)->toString();

        return $route;
    }
}
