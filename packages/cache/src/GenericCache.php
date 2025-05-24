<?php

namespace Tempest\Cache;

use Closure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Stringable;
use Tempest\Core\DeferredTasks;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\Support\Arr;
use Tempest\Support\Random;
use UnitEnum;

final class GenericCache implements Cache
{
    public function __construct(
        private(set) CacheItemPoolInterface $adapter,
        public bool $enabled = true,
        private ?DeferredTasks $deferredTasks = null,
        public null|UnitEnum|string $tag = null,
    ) {}

    public function lock(Stringable|string $key, null|Duration|DateTimeInterface $expiration = null, null|Stringable|string $owner = null): Lock
    {
        if ($expiration instanceof Duration) {
            $expiration = DateTime::now()->plus($expiration);
        }

        return new GenericLock(
            key: (string) $key,
            owner: $owner ? ((string) $owner) : Random\secure_string(length: 10),
            cache: $this,
            expiration: $expiration,
        );
    }

    public function has(Stringable|string $key): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return $this->adapter->getItem((string) $key)->isHit();
    }

    public function put(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        $item = $this->adapter
            ->getItem((string) $key)
            ->set($value);

        if ($expiration instanceof Duration) {
            $expiration = DateTime::now()->plus($expiration);
        }

        if ($expiration !== null) {
            $item = $item->expiresAt($expiration->toNativeDateTime());
        }

        if ($this->enabled) {
            $this->adapter->save($item);
        }

        return $item;
    }

    public function putMany(iterable $values, null|Duration|DateTimeInterface $expiration = null): array
    {
        $items = [];

        foreach ($values as $key => $value) {
            $items[(string) $key] = $this->put($key, $value, $expiration);
        }
        return $items;
    }

    public function increment(Stringable|string $key, int $by = 1): int
    {
        if (! $this->enabled) {
            return 0;
        }

        $item = $this->adapter->getItem((string) $key);

        if (! $item->isHit()) {
            $item->set($by);
        } elseif (! is_numeric($item->get())) {
            throw new NotNumberException((string) $key);
        } else {
            $item->set(((int) $item->get()) + $by);
        }

        $this->adapter->save($item);

        return (int) $item->get();
    }

    public function decrement(Stringable|string $key, int $by = 1): int
    {
        if (! $this->enabled) {
            return 0;
        }

        $item = $this->adapter->getItem((string) $key);

        if (! $item->isHit()) {
            $item->set(-$by);
        } elseif (! is_numeric($item->get())) {
            throw new NotNumberException((string) $key);
        } else {
            $item->set(((int) $item->get()) - $by);
        }

        $this->adapter->save($item);

        return (int) $item->get();
    }

    public function get(Stringable|string $key): mixed
    {
        if (! $this->enabled) {
            return null;
        }

        return $this->adapter->getItem((string) $key)->get();
    }

    public function getMany(iterable $key): array
    {
        return Arr\map_with_keys(
            array: $key,
            map: fn (string|Stringable $key) => yield (string) $key => $this->adapter->getItem((string) $key)->get(),
        );
    }

    public function resolve(Stringable|string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null, ?Duration $stale = null): mixed
    {
        if (! $this->enabled) {
            return $callback();
        }

        if ($stale) {
            if ($expiration instanceof Duration) {
                $expiration = DateTime::now()->plus($expiration);
            }

            return $this->resolveAllowingStale($key, $callback, $expiration, $stale);
        }

        $item = $this->adapter->getItem((string) $key);

        if (! $item->isHit()) {
            $item = $this->put((string) $key, $callback(), $expiration);
        }

        return $item->get();
    }

    private function resolveAllowingStale(Stringable|string $key, Closure $callback, DateTimeInterface $expiration, Duration $stale): mixed
    {
        if (! $this->deferredTasks) {
            return $this->resolve($key, $callback, $expiration);
        }

        $key = (string) $key;
        $staleAtCacheKey = "tempest.stale-cache.stale-at.{$key}";
        $cachedValue = $this->get($key);
        $cachedStaleAt = $this->get($staleAtCacheKey);

        // Not in the cache, save it
        if (! $cachedValue || ! $cachedStaleAt) {
            $value = $callback();

            $this->put($key, $value, $expiration->plus($stale));
            $this->put($staleAtCacheKey, $expiration->getTimestamp()->getSeconds(), $expiration->plus($stale));

            return $value;
        }

        // Not stale, return the value
        if ($cachedStaleAt > DateTime::now()->getTimestamp()->getSeconds()) {
            return $cachedValue;
        }

        // Stale, trigger refresh and return the value
        $this->deferredTasks->add(
            task: fn () => $this->lock("tempest.stale-cache.lock.{$key}")->execute(function () use ($callback, $key, $cachedStaleAt, $staleAtCacheKey, $stale, $expiration) {
                if ($cachedStaleAt !== $this->get($staleAtCacheKey)) {
                    return;
                }

                $this->put($key, $callback(), $expiration->plus($stale));
                $this->put($staleAtCacheKey, $expiration->getTimestamp()->getSeconds(), $expiration->plus($stale));
            }),
            name: "tempest.stale-cache.task.{$key}",
        );

        return $cachedValue;
    }

    public function remove(Stringable|string $key): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->adapter->deleteItem((string) $key);
    }

    public function clear(): void
    {
        if (! $this->adapter->clear()) {
            throw new CouldNotClearCache();
        }
    }
}
