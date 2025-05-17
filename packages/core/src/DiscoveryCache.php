<?php

declare(strict_types=1);

namespace Tempest\Core;

use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryItems;
use Throwable;

use function Tempest\internal_storage_path;

final class DiscoveryCache
{
    public bool $enabled {
        get => $this->valid && $this->strategy->isEnabled();
    }

    public bool $valid {
        get => $this->strategy->isValid();
    }

    public function __construct(
        private(set) DiscoveryCacheStrategy $strategy,
        private ?CacheItemPoolInterface $pool = null,
    ) {
        $this->pool = $pool ?? new PhpFilesAdapter(
            directory: internal_storage_path('cache/discovery'),
        );
    }

    public function restore(string $className): ?DiscoveryItems
    {
        if (! $this->enabled) {
            return null;
        }

        return $this->pool
            ->getItem(str_replace('\\', '_', $className))
            ->get();
    }

    public function store(Discovery $discovery, DiscoveryItems $discoveryItems): void
    {
        $key = str_replace('\\', '_', $discovery::class);

        $item = $this->pool
            ->getItem($key)
            ->set($discoveryItems);

        $this->pool->save($item);
    }

    public function clear(): void
    {
        if (! $this->pool->clear()) {
            throw new RuntimeException('Could not clear discovery cache.');
        }

        $this->storeStrategy(DiscoveryCacheStrategy::INVALID);
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
