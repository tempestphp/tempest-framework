<?php

declare(strict_types=1);

namespace Tempest\Core;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\DiscoveryCacheStrategy;
use Tempest\Cache\IsCache;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryItems;
use Throwable;
use function Tempest\internal_storage_path;

final class DiscoveryCache implements Cache
{
    use IsCache {
        clear as parentClear;
    }

    private CacheItemPoolInterface $pool;

    public function __construct(
        private readonly CacheConfig $cacheConfig,
        ?CacheItemPoolInterface $pool = null,
    ) {
        $this->pool = $pool ?? new PhpFilesAdapter(
            directory: $this->cacheConfig->directory . '/discovery',
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

        $item = $this->pool
            ->getItem($key)
            ->set($discoveryItems);

        $this->pool->save($item);
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->pool;
    }

    public function isEnabled(): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        if ($this->cacheConfig->enable) {
            return true;
        }

        return $this->cacheConfig->discoveryCache->isEnabled();
    }

    public function isValid(): bool
    {
        return $this->cacheConfig->discoveryCache->isValid();
    }

    public function clear(): void
    {
        $this->parentClear();

        $this->storeStrategy(DiscoveryCacheStrategy::INVALID);
    }

    public function getStrategy(): DiscoveryCacheStrategy
    {
        return $this->cacheConfig->discoveryCache;
    }

    public function storeStrategy(DiscoveryCacheStrategy $strategy): void
    {
        $dir = dirname(self::getCurrentDiscoverStrategyCachePath());

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        file_put_contents(self::getCurrentDiscoverStrategyCachePath(), $strategy->value);
    }

    public static function getCurrentDiscoverStrategyCachePath(): string
    {
        try {
            return internal_storage_path('current_discovery_strategy');
        } catch (Throwable) {
            return __DIR__ . '/current_discovery_strategy';
        }
    }
}
