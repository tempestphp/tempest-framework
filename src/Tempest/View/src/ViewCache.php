<?php

declare(strict_types=1);

namespace Tempest\View;

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

    public function getCachePool(): CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    protected function isEnabled(): bool
    {
        return $this->cacheConfig->enabled;
    }
}
