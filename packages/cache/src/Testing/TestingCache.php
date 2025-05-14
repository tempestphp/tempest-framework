<?php

namespace Tempest\Cache\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Cache\CacheItemInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\Config\CustomCacheConfig;
use Tempest\Cache\GenericCache;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class TestingCache implements Cache
{
    public bool $enabled {
        get => $this->cache->enabled;
        set => $this->cache->enabled = $value;
    }

    private Cache $cache;
    private ArrayAdapter $adapter;

    public function __construct(
        public string $tag,
        ClockInterface $clock,
    ) {
        $this->adapter = new ArrayAdapter(clock: $clock);
        $this->cache = new GenericCache(
            cacheConfig: new CustomCacheConfig(adapter: ArrayAdapter::class, tag: $tag),
            adapter: $this->adapter,
        );
    }

    public function put(string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        return $this->cache->put($key, $value, $expiration);
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function resolve(string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed
    {
        return $this->cache->resolve($key, $callback, $expiration);
    }

    public function remove(string $key): void
    {
        $this->cache->remove($key);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * Asserts that the given `$key` is cached.
     *
     * @param Closure(mixed $value): mixed $callback Optional callback to assert the value of the cached item.
     */
    public function assertCached(string $key, ?Closure $callback = null): self
    {
        Assert::assertTrue(
            condition: $this->get($key) !== null,
            message: "Cache key [{$key}] was not cached.",
        );

        if ($callback && false === $callback($this->get($key))) {
            Assert::fail("Cache key [{$key}] failed the assertion.");
        }

        return $this;
    }

    /**
     * Asserts that the given `$key` is not cached.
     */
    public function assertNotCached(string $key): self
    {
        Assert::assertFalse(
            condition: $this->get($key) !== null,
            message: "Cache key [{$key}] was cached.",
        );

        return $this;
    }

    /**
     * Asserts that the cache is empty.
     */
    public function assertEmpty(): self
    {
        Assert::assertTrue(
            condition: $this->adapter->getValues() === [],
            message: 'Cache is not empty.',
        );

        return $this;
    }

    /**
     * Asserts that the cache is not empty.
     */
    public function assertNotEmpty(): self
    {
        Assert::assertTrue(
            condition: $this->adapter->getValues() !== [],
            message: 'Cache is empty.',
        );

        return $this;
    }
}
