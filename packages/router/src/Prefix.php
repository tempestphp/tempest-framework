<?php

namespace Tempest\Router;

use Attribute;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use function Tempest\Support\path;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Prefix implements RouteDecorator
{
    public function __construct(
        private string $prefix
    ) {}

    public function decorate(DiscoveredRoute $route): DiscoveredRoute
    {
        $route->uri = path($this->prefix, $route->uri)->toString();

        return $route;
    }
}