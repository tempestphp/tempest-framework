<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Testing;

use Closure;
use Tempest\RateLimit\RateLimit;
use Tempest\RateLimit\RateLimiter;
use Tempest\RateLimit\RateLimitResult;

/**
 * Wraps another limiter and allows every attempt without recording it. Windows opened before
 * throttling was prevented are left as they were.
 */
final readonly class UnlimitedRateLimiter implements RateLimiter
{
    public function __construct(
        public RateLimiter $limiter,
    ) {}

    public function attempt(RateLimit $limit, int $by = 1): RateLimitResult
    {
        return $this->peek($limit);
    }

    public function peek(RateLimit $limit): RateLimitResult
    {
        return $this->allow($this->limiter->peek($limit));
    }

    public function throttle(RateLimit $limit, Closure $callback): mixed
    {
        return $callback();
    }

    public function clear(RateLimit $limit): void
    {
        $this->limiter->clear($limit);
    }

    private function allow(RateLimitResult $result): RateLimitResult
    {
        return new RateLimitResult(
            key: $result->key,
            allowed: true,
            limit: $result->limit,
            hits: $result->hits,
            resetsAtInSeconds: $result->resetsAtInSeconds,
            retryAfterInSeconds: 0,
        );
    }
}
