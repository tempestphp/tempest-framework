<?php

namespace Tempest\Cache\Config;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UnitEnum;

use function Tempest\internal_storage_path;

/**
 * Use the filesystem for caching.
 */
final class FilesystemCacheConfig implements CacheConfig
{
    public function __construct(
        /**
         * The directory where the cache files are stored.
         */
        public ?string $directory = null,

        /**
         * Optional namespace to avoid collisions with other caches in the same directory.
         */
        public ?string $namespace = null,

        /*
         * Identifies the {@see \Tempest\Cache\Cache} instance in the container, in case you need more than one configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return new FilesystemAdapter(
            namespace: $this->namespace ?? '',
            directory: $this->directory ?? internal_storage_path('/cache'),
        );
    }
}
