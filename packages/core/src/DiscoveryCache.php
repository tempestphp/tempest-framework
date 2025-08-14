<?php

declare(strict_types=1);

namespace Tempest\Core;

use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
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

    /**
     * @return array<class-string<\Tempest\Discovery\Discovery>, DiscoveryItems>
     */
    public function restore(DiscoveryLocation $location): ?array
    {
        if (! $this->enabled) {
            return null;
        }

        return $this->pool
            ->getItem($location->key)
            ->get();
    }

    /**
     * @param Discovery[] $discoveries
     */
    public function store(DiscoveryLocation $location, array $discoveries): void
    {
        $cachedForLocation = [];

        foreach ($discoveries as $discovery) {
            $items = $discovery->getItems();

            if ($this->strategy === DiscoveryCacheStrategy::PARTIAL) {
                $items = $items->onlyVendor();
            }

            $cachedForLocation[$discovery::class] = $items->getForLocation($location);
        }

        $item = $this->pool
            ->getItem($location->key)
            ->set($cachedForLocation);

        $saved = $this->pool->save($item);

        if (! $saved) {
            throw new CouldNotStoreDiscoveryCache($location);
        }
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
