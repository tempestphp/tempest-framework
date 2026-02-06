<?php

namespace Tempest\Cache\Testing;

use Closure;
use Psr\Cache\CacheItemInterface;
use Stringable;
use Tempest\Cache\Cache;
use Tempest\Cache\CacheUsageWasForbidden;
use Tempest\Cache\Lock;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use UnitEnum;

final class RestrictedCache implements Cache
{
    public bool $enabled;

    public function __construct(
        private null|string|UnitEnum $tag = null,
    ) {}

    private function resolveTag(): ?string
    {
        return $this->tag instanceof UnitEnum ? $this->tag->name : $this->tag;
    }

    public function lock(Stringable|string $key, null|Duration|DateTimeInterface $duration = null, null|Stringable|string $owner = null): Lock
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function has(Stringable|string $key): bool
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function put(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function putMany(iterable $values, null|Duration|DateTimeInterface $expiration = null): array
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function increment(Stringable|string $key, int $by = 1): int
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function decrement(Stringable|string $key, int $by = 1): int
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function get(Stringable|string $key): mixed
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function getMany(iterable $key): array
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function resolve(Stringable|string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null, ?Duration $stale = null): mixed
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function remove(Stringable|string $key): void
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }

    public function clear(): void
    {
        throw new CacheUsageWasForbidden($this->resolveTag());
    }
}
