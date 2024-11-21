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
    ) {
        $this->pool = $pool ?? new PhpFilesAdapter(
            directory: path($this->cacheConfig->directory, 'discovery')->toString(),
        );
    }

    public function restore(string $className): ?DiscoveryItems
    {
        if (isset($this->restored[$className])) {
            return $this->restored[$className];
        }

        $discoveryItems = $this->get(str_replace('\\', '_', $className));

        $this->restored[$className] = $discoveryItems;

        return $discoveryItems;
    }

    public function hasCache(Discovery $discovery, DiscoveryLocation $location): bool
    {
        $discoveryItems = $this->restore($discovery::class);

        if ($discoveryItems === null) {
            return false;
        }

        return $discoveryItems->hasLocation($location);
    }

    public function store(Discovery $discovery, DiscoveryItems $discoveryItems): void
    {
        $this->put(
            key: str_replace('\\', '_', $discovery::class),
            value: $discoveryItems,
        );
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
