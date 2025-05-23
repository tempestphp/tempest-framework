<?php

namespace Tempest\Cache\Config;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Tempest\Container\Container;
use Tempest\KeyValue\Redis\Redis;
use UnitEnum;

/**
 * Use a Redis connection for caching.
 */
final class RedisCacheConfig implements CacheConfig
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

    public function createAdapter(Container $container): RedisAdapter
    {
        return new RedisAdapter(
            redis: $container->get(Redis::class)->getClient(),
            namespace: $this->namespace ?? '',
        );
    }
}
