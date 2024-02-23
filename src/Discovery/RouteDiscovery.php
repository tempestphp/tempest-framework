<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use ReflectionMethod;
use function Tempest\attribute;
use Tempest\Container\Container;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;

final readonly class RouteDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/route-discovery.cache.php';

    public function __construct(private RouteConfig $routeConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttribute = attribute(Route::class)->in($method)->first();

            if (! $routeAttribute) {
                continue;
            }

            $this->routeConfig->addRoute($method, $routeAttribute);
        }
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->routeConfig->routes));
    }

    public function restoreCache(Container $container): void
    {
        $routes = unserialize(file_get_contents(self::CACHE_PATH));
        $this->routeConfig->routes = $routes;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
