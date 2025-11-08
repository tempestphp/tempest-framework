<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\RouteConfigurator;

final class RouteDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly RouteConfigurator $configurator,
        private readonly RouteConfig $routeConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $routeAttributes = $method->getAttributes(Route::class);

            foreach ($routeAttributes as $routeAttribute) {
                $decorators = [
                    ...$method->getDeclaringClass()->getAttributes(RouteDecorator::class),
                    ...$method->getAttributes(RouteDecorator::class),
                ];

                $this->discoveryItems->add($location, [$method, $routeAttribute, $decorators]);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $routeAttribute, $decorators]) {
            // TODO: maybe move discovered route creation to `discover`?
            $route = DiscoveredRoute::fromRoute($routeAttribute, $decorators, $method);
            $this->configurator->addRoute($route);
        }

        if ($this->configurator->isDirty()) {
            $this->routeConfig->apply($this->configurator->toRouteConfig());
        }
    }
}
