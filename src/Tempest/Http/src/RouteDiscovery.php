<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Http\Routing\Construction\RouteConfigurator;
use Tempest\Reflection\ClassReflector;

final readonly class RouteDiscovery implements Discovery
{
    public function __construct(
        private RouteConfigurator $configurator,
        private RouteConfig $routeConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
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
    public function finishDiscovery(): void
    {
        if ($this->configurator->isDirty()) {
            $this->routeConfig->apply($this->configurator->toRouteConfig());
        }
    }

    public function createCachePayload(): string
    {
        $this->finishDiscovery();

        return serialize($this->routeConfig);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $routeConfig = unserialize($payload, [ 'allowed_classes' => true ]);

        $this->routeConfig->apply($routeConfig);
    }
}
