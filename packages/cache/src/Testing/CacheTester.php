<?php

namespace Tempest\Cache\Testing;

use Tempest\Cache\Cache;
use Tempest\Cache\CacheInitializer;
use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use UnitEnum;

use function Tempest\Support\Str\to_kebab_case;

final readonly class CacheTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Forces the usage of a testing cache.
     */
    public function fake(null|string|UnitEnum $tag = null): TestingCache
    {
        $cache = new TestingCache(
            tag: match (true) {
                is_string($tag) => to_kebab_case($tag),
                $tag instanceof UnitEnum => to_kebab_case($tag->name),
                default => 'default',
            },
            clock: $this->container->get(Clock::class)->toPsrClock(),
        );

        $this->container->singleton(Cache::class, $cache, $tag);

        return $cache;
    }

    /**
     * Prevents cache from being used without a fake.
     */
    public function preventUsageWithoutFake(): void
    {
        if (! ($this->container instanceof GenericContainer)) {
            throw new \RuntimeException('Container is not a GenericContainer, unable to prevent usage without fake.');
        }

        $this->container->unregister(Cache::class, tagged: true);
        $this->container->removeInitializer(CacheInitializer::class);
        $this->container->addInitializer(RestrictedCacheInitializer::class);
    }
}
