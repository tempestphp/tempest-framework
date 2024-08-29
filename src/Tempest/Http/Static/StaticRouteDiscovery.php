<?php

namespace Tempest\Http\Static;

use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Http\StaticRoute;
use Tempest\Support\Reflection\ClassReflector;

final readonly class StaticRouteDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private StaticRouteConfig $staticRouteConfig,
    ) {}

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $staticRoute = $method->getAttribute(StaticRoute::class);

            if (! $staticRoute) {
                continue;
            }

            $this->staticRouteConfig->addHandler($staticRoute, $method);
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->staticRouteConfig->staticRoutes);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $this->staticRouteConfig->staticRoutes = unserialize($payload);
    }
}