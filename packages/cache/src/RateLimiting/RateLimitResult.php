<?php

declare(strict_types=1);

namespace Tempest\Cache\RateLimiting;

/**
 * The result of a rate limit check.
 */
final readonly class RateLimitResult
{
    public function __construct(
        /** Whether the request should be allowed. */
        public bool $allowed,
        /** The maximum number of attempts allowed. */
        public int $limit,
        /** The number of attempts remaining in the current window. */
        public int $remaining,
        /** The Unix timestamp when the rate limit resets. */
        public int $resetAt,
        /** The number of seconds until the rate limit resets. */
        public int $retryAfter,
    ) {}

    /**
     * Create a successful result (request allowed).
     */
    public static function allow(int $limit, int $remaining, int $resetAt): self
    {
        return new self(
            allowed: true,
            limit: $limit,
            remaining: $remaining,
            resetAt: $resetAt,
            retryAfter: max(0, $resetAt - time()),
        );
    }

    /**
     * Create a failed result (request denied due to rate limit).
     */
    public static function deny(int $limit, int $resetAt): self
    {
        return new self(
            allowed: false,
            limit: $limit,
            remaining: 0,
            resetAt: $resetAt,
            retryAfter: max(0, $resetAt - time()),
        );
    }
}
