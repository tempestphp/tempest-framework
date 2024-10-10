<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;

final class GenericCache implements Cache
{
    use IsCache;

    public function __construct(
        private readonly CacheConfig $cacheConfig,
    ) {
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->cacheConfig->pool;
    }

    protected function isEnabled(): bool
    {
        return $this->cacheConfig->enabled;
    }
}
