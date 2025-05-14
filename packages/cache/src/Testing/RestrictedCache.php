<?php

namespace Tempest\Cache\Testing;

use Closure;
use Psr\Cache\CacheItemInterface;
use Tempest\Cache\Cache;
use Tempest\Cache\ForbiddenCacheUsageException;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class RestrictedCache implements Cache
{
    public bool $enabled;
    public function __construct(
        private ?string $tag = null,
    ) {}

    public function put(string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function get(string $key): mixed
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function resolve(string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function remove(string $key): void
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }

    public function clear(): void
    {
        throw new ForbiddenCacheUsageException($this->tag);
    }
}
