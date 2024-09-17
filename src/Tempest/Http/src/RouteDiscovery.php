<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\VarExport\VarExportPhpFile;

final readonly class RouteDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/../../../../.cache/tempest/route-discovery.cache.php';

    /** @var VarExportPhpFile<RouteConfig> */
    private VarExportPhpFile $routeCacheFile;

    public function __construct(private RouteConfig $routeConfig)
    {
        $this->routeCacheFile = new VarExportPhpFile(self::CACHE_PATH);
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

    public function hasCache(): bool
    {
        return $this->routeCacheFile->exists();
    }

    public function storeCache(): void
    {
        $this->routeCacheFile->export($this->routeConfig);
    }

    public function restoreCache(Container $container): void
    {
        $cachedRouteConfig = $this->routeCacheFile->import();
        $this->routeConfig->staticRoutes = $cachedRouteConfig->staticRoutes;
        $this->routeConfig->dynamicRoutes = $cachedRouteConfig->dynamicRoutes;
        $this->routeConfig->matchingRegexes = $cachedRouteConfig->matchingRegexes;
    }

    public function destroyCache(): void
    {
        $this->routeCacheFile->destroy();
    }
}
