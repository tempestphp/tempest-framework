<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\RateLimit;

use Tempest\DateTime\Duration;
use Tempest\RateLimit\RateLimitStorage;
use Tempest\RateLimit\Storage\RateLimitState;
use Tempest\RateLimit\Storage\RateLimitStorageFailed;

/**
 * Storage that cannot be reached, standing in for a store that is down.
 */
final readonly class UnreachableRateLimitStorage implements RateLimitStorage
{
    public function find(string $key): ?RateLimitState
    {
        throw new RateLimitStorageFailed($key, 'the storage is unreachable');
    }

    public function increment(string $key, Duration $window, int $by = 1): RateLimitState
    {
        throw new RateLimitStorageFailed($key, 'the storage is unreachable');
    }

    public function remove(string $key): void
    {
        throw new RateLimitStorageFailed($key, 'the storage is unreachable');
    }
}
