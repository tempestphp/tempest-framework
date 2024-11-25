<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\IsDiscovery;
use Tempest\Http\Routing\Construction\RouteConfigurator;
use Tempest\Reflection\ClassReflector;

final class RouteDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly RouteConfigurator $configurator,
        private readonly RouteConfig $routeConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $routeAttributes = $method->getAttributes(Route::class);

            foreach ($routeAttributes as $routeAttribute) {
                $this->discoveryItems->add($location, [$method, $routeAttribute]);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $routeAttribute]) {
            $routeAttribute->setHandler($method);
            $this->configurator->addRoute($routeAttribute);
        }

        if ($this->configurator->isDirty()) {
            $this->routeConfig->apply($this->configurator->toRouteConfig());
        }
    }
}
