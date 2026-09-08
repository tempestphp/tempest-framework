<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Attribute;
use Tempest\Container\Container;
use Tempest\Http\Request;
use Tempest\RateLimit\Per;
use Tempest\RateLimit\RateLimit;

/**
 * Limits how often a route may be requested. The attribute is repeatable: a route may be subject to several limits at once.
 *
 * ```php
 * #[Throttle(attempts: 60)]
 * #[Throttle(attempts: 1000, per: Per::DAY)]
 * #[Get('/api/posts')]
 * public function index(): Response { /* … *\/ }
 * ```
 *
 * When the limits depend on the request itself, use {@see ThrottleWith} instead.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Throttle implements Throttles
{
    use AddsThrottleMiddleware;

    public function __construct(
        /**
         * The maximum amount of requests allowed within the window.
         */
        public int $attempts,

        /**
         * The unit of time the window is expressed in.
         */
        public Per $per = Per::MINUTE,

        /**
         * How many `$per` units the window spans. For instance, `per: Per::MINUTE, every: 5` is five minutes.
         */
        public int $every = 1,

        /**
         * Groups this limit with the ones naming the same bucket: those routes spend from a single
         * allowance, per client. Without a name, the limit gets its own counter, scoped to what it
         * was declared on. Either way the counter is scoped to the client, so it cannot be addressed
         * through {@see \Tempest\RateLimit\RateLimiter}; use a {@see RateLimitProfile} for that.
         */
        public ?string $bucket = null,
    ) {}

    public function resolveLimits(Request $request, Container $container): array
    {
        return [$this->toRateLimit()];
    }

    /**
     * Returns the rate limit described by this attribute. The bucket is not part of it: it groups
     * routes rather than naming a counter, and is applied by {@see ThrottleCounterKey}.
     */
    public function toRateLimit(): RateLimit
    {
        return new RateLimit(
            attempts: $this->attempts,
            window: $this->per->toDuration($this->every),
        );
    }
}
