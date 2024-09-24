<?php

namespace Tempest\Cache;

use Closure;
use DateTimeInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

trait IsCache
{
    protected abstract function getCachePool(): CacheItemPoolInterface;

    public function put(string $key, mixed $value, ?DateTimeInterface $expiresAt = null): CacheItem
    {
        $item = $this->getCachePool()
            ->getItem($key)
            ->set($value);

        if ($expiresAt !== null) {
            $item = $item->expiresAt($expiresAt);
        }

        $this->getCachePool()->save($item);

        return $item;
    }

    public function get(string $key): mixed
    {
        return $this->getCachePool()->getItem($key)->get();
    }

    /** @param Closure(): mixed $cache */
    public function resolve(string $key, Closure $cache, DateTimeInterface $expiresAt = null): mixed
    {
        $item = $this->getCachePool()->getItem($key);

        if (! $item->isHit()) {
            $item = $this->put($key, $cache(), $expiresAt);
        }

        return $item->get();
    }

    public function remove(string $key): void
    {
        $this->getCachePool()->deleteItem($key);
    }

    public function clear(): void
    {
        if (! $this->getCachePool()->clear()) {
            throw new CouldNotClearCache();
        }
    }
}