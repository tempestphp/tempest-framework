<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests\Fixtures;

use Closure;
use Psr\Cache\CacheItemInterface;
use Stringable;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\GenericCache;
use Tempest\Cache\Lock;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class RecordingCache implements Cache
{
    public ?Duration $lastLockDuration = null;

    private readonly GenericCache $cache;

    public bool $enabled = true;

    public function __construct()
    {
        $this->cache = new GenericCache(new ArrayAdapter());
    }

    public function lock(Stringable|string $key, Duration|DateTimeInterface|null $duration = null, Stringable|string|null $owner = null): Lock
    {
        $this->lastLockDuration = $duration instanceof Duration ? $duration : null;

        return $this->cache->lock($key, $duration, $owner);
    }

    public function has(Stringable|string $key): bool
    {
        return $this->cache->has($key);
    }

    public function put(Stringable|string $key, mixed $value, Duration|DateTimeInterface|null $expiration = null): CacheItemInterface
    {
        return $this->cache->put($key, $value, $expiration);
    }

    public function putMany(iterable $values, Duration|DateTimeInterface|null $expiration = null): array
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

    public function resolve(Stringable|string $key, Closure $callback, Duration|DateTimeInterface|null $expiration = null, ?Duration $stale = null): mixed
    {
        return $this->cache->resolve($key, $callback, $expiration, $stale);
    }

    public function remove(Stringable|string $key): void
    {
        $this->cache->remove($key);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }
}
