<?php

namespace Tempest\Cache;

use Closure;
use DateTimeInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

final readonly class Cache
{
    public function __construct(
        private CacheItemPoolInterface $pool,
    ) {}

    public function put(string $key, mixed $value, DateTimeInterface $expiresAt): CacheItem
    {
        $item = $this->pool
            ->getItem($key)
            ->set($value)
            ->expiresAt($expiresAt);

        $this->pool->save($item);

        return $item;
    }

    /** @param Closure(): mixed $cache */
    public function get(string $key, Closure $cache, DateTimeInterface $expiresAt = null): mixed
    {
        $item = $this->pool->getItem($key);

        if (! $item->isHit()) {
            $item = $this->put($key, $cache(), $expiresAt);
        }

        return $item->get();
    }

    public function remove(string $key): void
    {
        $this->pool->deleteItem($key);
    }

    public function clear(): void
    {
        if (! $this->pool->clear()) {
            throw new CouldNotClearCache();
        }
    }
}