<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Tempest\Core\DiscoveryCache;
use function Tempest\env;

final class CacheConfig
{
    /** @var class-string<\Tempest\Cache\Cache>[] */
    public array $caches = [];

    public ?bool $enable;

    public bool $projectCache = false;

    public bool $viewCache = false;

    public DiscoveryCacheStrategy $discoveryCache;

    public function __construct(
        public string $directory = __DIR__ . '/../../../../.cache',
        public ?CacheItemPoolInterface $projectCachePool = null,

        /** Used as a global override, should be true in production, null in local */
        ?bool $enable = null,
    ) {
        $this->enable = $enable ?? env('CACHE') ?? null;
        $this->projectCache = (bool) env('PROJECT_CACHE', false);
        $this->viewCache = (bool) env('VIEW_CACHE', false);
        $this->discoveryCache = $this->resolveDiscoveryCacheStrategy();
    }

    /** @param class-string<\Tempest\Cache\Cache> $className */
    public function addCache(string $className): void
    {
        $this->caches[] = $className;
    }

    private function resolveDiscoveryCacheStrategy(): DiscoveryCacheStrategy
    {
        if (PHP_SAPI === 'cli') {
            $command = $_SERVER['argv'][1] ?? null;

            if ($command === 'dg' || $command === 'discovery:generate') {
                return DiscoveryCacheStrategy::NONE;
            }
        }

        $cache = env('CACHE');

        if ($cache !== null) {
            $current = DiscoveryCacheStrategy::make($cache);
        } else {
            $current = DiscoveryCacheStrategy::make(env('DISCOVERY_CACHE'));
        }

        if ($current === DiscoveryCacheStrategy::NONE) {
            return $current;
        }

        $original = DiscoveryCacheStrategy::make(@file_get_contents(DiscoveryCache::CURRENT_DISCOVERY_STRATEGY));

        if ($current !== $original) {
            return DiscoveryCacheStrategy::INVALID;
        }

        return $current;
    }
}
