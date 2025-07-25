<?php

namespace Tempest\Cache\Config;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Clock\Clock;
use Tempest\Container\Container;
use UnitEnum;

/**
 * Store cache in an array, for a single request.
 */
final class InMemoryCacheConfig implements CacheConfig
{
    public function __construct(
        /*
         * Identifies the {@see \Tempest\Cache\Cache} instance in the container, in case you need more than one configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createAdapter(Container $container): ArrayAdapter
    {
        return new ArrayAdapter(
            clock: $container->get(Clock::class)->toPsrClock(),
        );
    }
}
