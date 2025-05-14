<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Closure;
use Psr\Cache\CacheItemInterface;
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
    public function put(string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface;

    /**
     * Gets the value associated with the specified key from the cache. If the key does not exist, null is returned.
     */
    public function get(string $key): mixed;

    /**
     * If the specified key already exists in the cache, the value is returned and the `$callback` is not executed. Otherwise, the result of the callback is stored, then returned.
     */
    public function resolve(string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed;

    /**
     * Removes the specified key from the cache.
     */
    public function remove(string $key): void;

    /**
     * Clears the entire cache.
     */
    public function clear(): void;
}
