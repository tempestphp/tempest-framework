<?php

namespace Tempest\Cache\Testing;

use Closure;
use Psr\Cache\CacheItemInterface;
use Stringable;
use Tempest\Cache\Cache;
use Tempest\Cache\ForbiddenCacheUsageException;
use Tempest\Cache\Lock;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class RestrictedCache implements Cache
{
    public bool $enabled;
    public function __construct(
        private ?string $tag = null,
    ) {}

    public function lock(Stringable|string $key, null|Duration|DateTimeInterface $expiration = null, null|Stringable|string $owner = null): Lock
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function has(Stringable|string $key): bool
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function put(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function putMany(iterable $values, null|Duration|DateTimeInterface $expiration = null): array
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function increment(Stringable|string $key, int $by = 1): int
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function decrement(Stringable|string $key, int $by = 1): int
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function get(Stringable|string $key): mixed
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function getMany(iterable $key): array
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function resolve(Stringable|string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function remove(Stringable|string $key): void
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function clear(): void
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }
}
