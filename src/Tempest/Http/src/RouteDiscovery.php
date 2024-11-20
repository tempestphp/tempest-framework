<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\IsDiscovery;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Http\Routing\Construction\RouteConfigurator;
use Tempest\Reflection\ClassReflector;

final readonly class RouteDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private RouteConfigurator $configurator,
        private RouteConfig $routeConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $routeAttributes = $method->getAttributes(Route::class);

            foreach ($routeAttributes as $routeAttribute) {
                $routeAttribute->setHandler($method);

                $this->configurator->addRoute($routeAttribute);
            }
        }
    }

    #[EventHandler(KernelEvent::BOOTED)]
    public function apply(): void
    {
        if ($this->configurator->isDirty()) {
            $this->routeConfig->apply($this->configurator->toRouteConfig());
        }
    }
}
