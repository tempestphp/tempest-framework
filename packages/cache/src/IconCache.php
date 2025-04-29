<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class IconCache implements Cache
{
    use IsCache;

    private CacheItemPoolInterface $pool;

    public function __construct(
        private readonly CacheConfig $cacheConfig,
    ) {
        $this->pool = new FilesystemAdapter(
            directory: $this->cacheConfig->directory . '/icons',
        );
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->pool;
    }

    public function isEnabled(): bool
    {
        return $this->cacheConfig->enable ?? $this->cacheConfig->iconCache;
    }
}
