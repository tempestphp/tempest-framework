<?php

namespace Tempest\Cache\Config;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Clock\Clock;
use UnitEnum;

use function Tempest\get;

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

    public function createAdapter(): ArrayAdapter
    {
        return new ArrayAdapter(
            clock: get(Clock::class)->toPsrClock(),
        );
    }
}
