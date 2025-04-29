<?php

namespace Tempest\Router;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class HttpMiddlewareDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly RouteConfig $routeConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(HttpMiddleware::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        $this->routeConfig->middleware->add(...$this->discoveryItems);
    }
}
