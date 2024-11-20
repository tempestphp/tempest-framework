<?php

declare(strict_types=1);

namespace Tempest\Core;

use DateTimeInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\IsCache;
use function Tempest\path;

final readonly class DiscoveryCache implements Cache
{
    use IsCache {
        IsCache::get as getParent;
        IsCache::put as putParent;
    }

    private CacheItemPoolInterface $pool;

    public function __construct(
        private CacheConfig $cacheConfig,
        ?CacheItemPoolInterface $pool = null,
    ) {
        $this->pool = $pool ?? new FilesystemAdapter(
            directory: path($this->cacheConfig->directory, 'discovery')->toString(),
        );
    }

    public function get(string $key): mixed
    {
        return $this->getParent(str_replace('\\', '_', $key));
    }

    public function put(string $key, mixed $value, ?DateTimeInterface $expiresAt = null): CacheItemInterface
    {
        return $this->putParent(str_replace('\\', '_', $key), $value, $expiresAt);
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->pool;
    }

    public function isEnabled(): bool
    {
        return $this->cacheConfig->enable ?? $this->cacheConfig->discoveryCache;
    }
}
