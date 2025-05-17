<?php

namespace Tempest\Cache\Config;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use UnitEnum;

use function Tempest\get;

/**
 * Use a custom adapter as the cache backend.
 */
final class CustomCacheConfig implements CacheConfig
{
    public function __construct(
        /**
         * FQCN of the custom cache adapter, resolved through the container.
         *
         * @param class-string<AdapterInterface>
         */
        private string $adapter,

        /*
         * Identifies the {@see \Tempest\Cache\Cache} instance in the container, in case you need more than one configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createAdapter(): AdapterInterface
    {
        return get($this->adapter);
    }
}
