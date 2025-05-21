<?php

namespace Tempest\Cache\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use Psr\Cache\CacheItemInterface;
use Psr\Clock\ClockInterface;
use Stringable;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\Config\CustomCacheConfig;
use Tempest\Cache\GenericCache;
use Tempest\Cache\GenericLock;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\Support\Random;

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

    public function lock(Stringable|string $key, null|Duration|DateTimeInterface $expiration = null, null|Stringable|string $owner = null): TestingLock
    {
        return new TestingLock(new GenericLock(
            key: (string) $key,
            owner: $owner ? ((string) $owner) : Random\secure_string(length: 10),
            cache: $this->cache,
            expiration: $expiration,
        ));
    }

    public function has(Stringable|string $key): bool
    {
        return $this->cache->has($key);
    }

    public function put(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        return $this->cache->put($key, $value, $expiration);
    }

    public function putMany(iterable $values, null|Duration|DateTimeInterface $expiration = null): array
    {
        return $this->cache->putMany($values, $expiration);
    }

    public function increment(Stringable|string $key, int $by = 1): int
    {
        return $this->cache->increment($key, $by);
    }

    public function decrement(Stringable|string $key, int $by = 1): int
    {
        return $this->cache->decrement($key, $by);
    }

    public function get(Stringable|string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function getMany(iterable $key): array
    {
        return $this->cache->getMany($key);
    }

    public function resolve(Stringable|string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed
    {
        return $this->cache->resolve($key, $callback, $expiration);
    }

    public function remove(Stringable|string $key): void
    {
        $this->cache->remove($key);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * Asserts that the given `$key` is cached and matches the expected `$value`.
     */
    public function assertKeyHasValue(Stringable|string $key, mixed $value): self
    {
        $this->assertCached($key);

        Assert::assertSame(
            expected: $value,
            actual: $this->get($key),
            message: "Cache key [{$key}] does not match the expected value.",
        );

        return $this;
    }

    /**
     * Asserts that the given `$key` is cached and does not match the given `$value`.
     */
    public function assertKeyDoesNotHaveValue(Stringable|string $key, mixed $value): self
    {
        $this->assertCached($key);

        Assert::assertNotSame(
            expected: $value,
            actual: $this->get($key),
            message: "Cache key [{$key}] matches the given value.",
        );

        return $this;
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

    /**
     * Asserts that the specified lock is being held.
     */
    public function assertLocked(string|Stringable $key, null|Stringable|string $by = null, null|DateTimeInterface|Duration $until = null): self
    {
        $this->lock($key)->assertLocked($by, $until);

        return $this;
    }

    /**
     * Asserts that the specified lock is not being held.
     */
    public function assertNotLocked(string|Stringable $key, null|Stringable|string $by = null): self
    {
        $this->lock($key)->assertNotLocked($by);

        return $this;
    }
}
