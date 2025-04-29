<?php

declare(strict_types=1);

namespace Tempest\View;

use Closure;
use Psr\Cache\CacheItemPoolInterface;
use Tempest\Cache\Cache;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\IsCache;

use function Tempest\Support\path;

final class ViewCache implements Cache
{
    use IsCache;

    private readonly ViewCachePool $cachePool;

    public function __construct(
        private readonly CacheConfig $cacheConfig,
        ?ViewCachePool $pool = null,
    ) {
        $this->cachePool = $pool ?? new ViewCachePool(
            directory: $this->cacheConfig->directory . '/views',
        );
    }

    public function getCachedViewPath(string $path, Closure $compiledView): string
    {
        $cacheKey = (string) crc32($path);

        $cacheItem = $this->cachePool->getItem($cacheKey);

        if ($this->isEnabled() === false || $cacheItem->isHit() === false) {
            $cacheItem->set($compiledView());

            $this->cachePool->save($cacheItem);
        }

        return path($this->cachePool->directory, $cacheItem->getKey() . '.php')->toString();
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    public function isEnabled(): bool
    {
        return $this->cacheConfig->enable ?? $this->cacheConfig->viewCache;
    }
}
