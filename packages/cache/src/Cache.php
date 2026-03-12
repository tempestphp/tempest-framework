<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Closure;
use Psr\Cache\CacheItemInterface;
use Stringable;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

interface Cache
{
    /**
     * Whether the cache is enabled.
     */
    public bool $enabled {
        get;
        set;
    }

    /**
     * Returns a lock for the specified key. The lock is not acquired until `acquire()` is called.
     *
     * @param Stringable|string $key The identifier of the lock.
     * @param null|Duration|DateTimeInterface $duration The duration for the lock, or an expiration date from which the duration will be calculated. If not specified, the lock will not expire.
     * @param null|Stringable|string $owner The owner of the lock, which will be used to identify the process releasing it. If not specified, a random string will be used.
     */
    public function lock(Stringable|string $key, null|Duration|DateTimeInterface $duration = null, null|Stringable|string $owner = null): Lock;

    /**
     * Sets the specified key to the specified value in the cache. Optionally, specify an expiration.
     */
    public function put(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface;

    /**
     * Sets the specified keys to the specified values in the cache. Optionally, specify an expiration.
     *
     * @template TKey of Stringable|string
     * @template TValue
     *
     * @param iterable<TKey,TValue> $values
     * @return array<string,CacheItemInterface>
     */
    public function putMany(iterable $values, null|Duration|DateTimeInterface $expiration = null): array;

    /**
     * Gets the value associated with the specified key from the cache. If the key does not exist, null is returned.
     */
    public function get(Stringable|string $key): mixed;

    /**
     * Gets the values associated with the specified keys from the cache. If a key does not exist, null is returned for that key.
     *
     * @template TKey of Stringable|string
     *
     * @param iterable<array-key, TKey> $key
     * @return array<string,mixed>
     */
    public function getMany(iterable $key): array;

    /**
     * Determines whether the cache contains the specified key.
     */
    public function has(Stringable|string $key): bool;

    /**
     * Increments the value associated with the specified key by the specified amount. If the key does not exist, it is created with the specified amount.
     */
    public function increment(Stringable|string $key, int $by = 1): int;

    /**
     * Decrements the value associated with the specified key by the specified amount. If the key does not exist, it is created with the negative amount.
     */
    public function decrement(Stringable|string $key, int $by = 1): int;

    /**
     * If the specified key already exists in the cache, the value is returned and the `$callback` is not executed. Otherwise, the result of the callback is stored, then returned.
     *
     * @param null|Duration $stale Allow the value to be stale for the specified amount of time in addition to the time-to-live specified by `$expiration`. When a value is stale, it will still be returned as-is, but it will be refreshed in the background.
     */
    public function resolve(Stringable|string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null, ?Duration $stale = null): mixed;

    /**
     * Removes the specified key from the cache.
     */
    public function remove(Stringable|string $key): void;

    /**
     * Clears the entire cache.
     */
    public function clear(): void;
}
