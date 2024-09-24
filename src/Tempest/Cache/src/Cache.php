<?php

namespace Tempest\Cache;

use Closure;
use DateTimeInterface;
use Symfony\Component\Cache\CacheItem;

interface Cache
{
    public function put(string $key, mixed $value, ?DateTimeInterface $expiresAt = null): CacheItem;

    public function get(string $key): mixed;

    /** @param Closure(): mixed $cache */
    public function resolve(string $key, Closure $cache, DateTimeInterface $expiresAt = null): mixed;

    public function remove(string $key): void;

    public function clear(): void;
}