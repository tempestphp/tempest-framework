<?php

namespace Tempest\Cache\Config;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Container\Container;
use UnitEnum;

use function Tempest\internal_storage_path;

/**
 * Use the filesystem for caching, but encode content as native PHP code.
 */
final class PhpCacheConfig implements CacheConfig
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

    public function createAdapter(Container $container): PhpFilesAdapter
    {
        return new PhpFilesAdapter(
            namespace: $this->namespace ?? '',
            directory: $this->directory ?? internal_storage_path('cache/project'),
        );
    }
}
