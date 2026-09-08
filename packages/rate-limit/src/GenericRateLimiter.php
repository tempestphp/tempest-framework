<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Closure;
use Tempest\Clock\Clock;
use Tempest\RateLimit\Storage\RateLimitState;

final readonly class GenericRateLimiter implements RateLimiter
{
    public function __construct(
        private RateLimitStorage $storage,
        private Clock $clock,
    ) {}

    public function attempt(RateLimit $limit, int $by = 1): RateLimitResult
    {
        // Records before evaluating: an expired window must not be extended.
        return $this->toResult($limit, $this->storage->increment($this->key($limit), $limit->window, $by), consumed: true);
    }

    public function peek(RateLimit $limit): RateLimitResult
    {
        return $this->toResult($limit, $this->storage->find($this->key($limit)), consumed: false);
    }

    public function throttle(RateLimit $limit, Closure $callback): mixed
    {
        $result = $this->attempt($limit);

        if ($result->exceeded) {
            throw new RateLimitWasExceeded($result);
        }

        return $callback();
    }

    public function clear(RateLimit $limit): void
    {
        $this->storage->remove($this->key($limit));
    }

    /**
     * Returns the key the limit is counted under. Keyless limits are rejected rather than guessed at,
     * as they would all share a single counter.
     */
    private function key(RateLimit $limit): string
    {
        return $limit->key ?? throw new RateLimitKeyWasMissing($limit);
    }

    /**
     * @param bool $consumed Whether `$state` already includes the attempt being evaluated.
     */
    private function toResult(RateLimit $limit, ?RateLimitState $state, bool $consumed): RateLimitResult
    {
        // Nothing has been counted yet, and no window is open. Opening one here would report a
        // reset for a window that no attempt belongs to.
        $state ??= new RateLimitState(hits: 0, resetsAtInSeconds: $this->clock->seconds());
        $allowed = $consumed
            ? $state->hits <= $limit->attempts
            : $state->hits < $limit->attempts;

        return new RateLimitResult(
            key: $this->key($limit),
            allowed: $allowed,
            limit: $limit->attempts,
            hits: $state->hits,
            resetsAtInSeconds: $state->resetsAtInSeconds,
            // Only a rejected attempt has to wait. Reporting a delay on an allowed one would have
            // a client back off while it still has attempts left.
            retryAfterInSeconds: $allowed ? 0 : max(0, $state->resetsAtInSeconds - $this->clock->seconds()),
        );
    }
}
