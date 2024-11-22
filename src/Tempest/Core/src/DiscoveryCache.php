<?php

declare(strict_types=1);

namespace Tempest\Core;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\DiscoveryCacheStrategy;
use Tempest\Cache\IsCache;
use function Tempest\path;

final class DiscoveryCache implements Cache
{
    use IsCache;

    private CacheItemPoolInterface $pool;

    private array $restored = [];

    public function __construct(
        private readonly CacheConfig $cacheConfig,
        ?CacheItemPoolInterface $pool = null,
    )
    {
        $this->pool = $pool ?? new PhpFilesAdapter(
            directory: path($this->cacheConfig->directory, 'discovery')->toString(),
        );
    }

    public function restore(string $className): ?DiscoveryItems
    {
        $key = str_replace('\\', '_', $className);

        return $this->get($key);
    }

    public function store(Discovery $discovery, DiscoveryItems $discoveryItems): void
    {
        $key = str_replace('\\', '_', $discovery::class);

        $item = $this->getCachePool()
            ->getItem($key)
            ->set($discoveryItems);

        $this->getCachePool()->save($item);
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->pool;
    }

    public function isEnabled(): bool
    {
        if ($this->cacheConfig->enable) {
            return true;
        }

        return $this->cacheConfig->discoveryCache !== DiscoveryCacheStrategy::NONE;
    }

    public function getStrategy(): DiscoveryCacheStrategy
    {
        return $this->cacheConfig->discoveryCache;
    }
}
