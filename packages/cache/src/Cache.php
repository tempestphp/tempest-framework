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
     * Sets the specified key to the specified value in the cache. Optionally, specify an expiration.
     */
    public function put(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface;

    /**
     * Sets the specified keys to the specified values in the cache. Optionally, specify an expiration.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @return array<TKey,CacheItemInterface>
     */
    public function putMany(iterable $values, null|Duration|DateTimeInterface $expiration = null): array;

    /**
     * Gets the value associated with the specified key from the cache. If the key does not exist, null is returned.
     */
    public function get(Stringable|string $key): mixed;

    /**
     * Gets the values associated with the specified keys from the cache. If a key does not exist, null is returned for that key.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @return array<TValue,mixed>
     */
    public function getMany(iterable $key): array;

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
     */
    public function resolve(Stringable|string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed;

    /**
     * Removes the specified key from the cache.
     */
    public function remove(Stringable|string $key): void;

    /**
     * Clears the entire cache.
     */
    public function clear(): void;
}
