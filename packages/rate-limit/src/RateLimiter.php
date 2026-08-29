<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Closure;

interface RateLimiter
{
    /**
     * Records an attempt against the specified rate limit and returns whether it was allowed. The
     * attempt is recorded even when the limit is exceeded, but never extends the window.
     */
    public function attempt(RateLimit $limit, int $by = 1): RateLimitResult;

    /**
     * Returns the current state of the specified rate limit without recording an attempt.
     */
    public function peek(RateLimit $limit): RateLimitResult;

    /**
     * Executes the callback if the specified rate limit allows it, throwing {@see RateLimitWasExceeded} otherwise.
     * Rejections are the caller's to handle; only {@see \Tempest\RateLimit\Http\Throttle} turns one into a response.
     *
     * @template TReturn
     *
     * @param Closure(): TReturn $callback
     * @return TReturn
     */
    public function throttle(RateLimit $limit, Closure $callback): mixed;

    /**
     * Discards the attempts recorded for the specified rate limit.
     */
    public function clear(RateLimit $limit): void;
}
