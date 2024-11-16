<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use function Tempest\path;

final class ProjectCache implements Cache
{
    use IsCache;

    private CacheItemPoolInterface $pool;

    public function __construct(
        private readonly CacheConfig $cacheConfig,
    ) {
        $this->pool = $this->cacheConfig->projectCachePool ?? new FilesystemAdapter(
            directory: path($this->cacheConfig->directory, 'project')->toString(),
        );
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->pool;
    }

    public function isEnabled(): bool
    {
        return $this->cacheConfig->enable ?? $this->cacheConfig->projectCache;
    }
}
