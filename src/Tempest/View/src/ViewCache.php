<?php

declare(strict_types=1);

namespace Tempest\View;

use Closure;
use Psr\Cache\CacheItemPoolInterface;
use Tempest\Cache\Cache;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\IsCache;

final class ViewCache implements Cache
{
    use IsCache;

    private readonly CacheItemPoolInterface $cachePool;

    public function __construct(
        private readonly CacheConfig $cacheConfig
    ) {
        $this->cachePool = new ViewCachePool();
    }

    public function getCachedViewPath(string $path, Closure $compiledView): string
    {
        $cacheKey = (string)crc32($path);

        $cacheItem = $this->cachePool->getItem($cacheKey);

        if ($this->cacheConfig->enabled === false || $cacheItem->isHit() === false) {
            $cacheItem = $this->put($cacheKey, $compiledView());
        }

        return __DIR__ . '/.cache/' . $cacheItem->getKey() . '.php';
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    protected function isEnabled(): bool
    {
        return $this->cacheConfig->enabled;
    }
}
