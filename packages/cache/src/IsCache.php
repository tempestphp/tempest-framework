<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Closure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tempest\DateTime\DateTimeInterface;

trait IsCache
{
    abstract protected function getCachePool(): CacheItemPoolInterface;

    public function put(string $key, mixed $value, ?DateTimeInterface $expiresAt = null): CacheItemInterface
    {
        $item = $this->getCachePool()
            ->getItem($key)
            ->set($value);

        if ($expiresAt !== null) {
            $item = $item->expiresAt($expiresAt->toNativeDateTime());
        }

        if ($this->isEnabled()) {
            $this->getCachePool()->save($item);
        }

        return $item;
    }

    public function get(string $key): mixed
    {
        if (! $this->isEnabled()) {
            return null;
        }

        return $this->getCachePool()->getItem($key)->get();
    }

    /** @param Closure(): mixed $cache */
    public function resolve(string $key, Closure $cache, ?DateTimeInterface $expiresAt = null): mixed
    {
        if (! $this->isEnabled()) {
            return $cache();
        }

        $item = $this->getCachePool()->getItem($key);

        if (! $item->isHit()) {
            $item = $this->put($key, $cache(), $expiresAt);
        }

        return $item->get();
    }

    public function remove(string $key): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->getCachePool()->deleteItem($key);
    }

    public function clear(): void
    {
        if (! $this->getCachePool()->clear()) {
            throw new CouldNotClearCache();
        }
    }
}
