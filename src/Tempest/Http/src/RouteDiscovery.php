<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Reflection\ClassReflector;

final readonly class RouteDiscovery implements Discovery
{
    public function __construct(
        private RouteConfig $routeConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $routeAttribute = $method->getAttribute(Route::class);

            if (! $routeAttribute) {
                continue;
            }

            $this->routeConfig->addRoute($method, $routeAttribute);
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->routeConfig);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $routeConfig = unserialize($payload);

        $this->routeConfig->staticRoutes = $routeConfig->staticRoutes;
        $this->routeConfig->dynamicRoutes = $routeConfig->dynamicRoutes;
        $this->routeConfig->matchingRegexes = $routeConfig->matchingRegexes;
    }
}
