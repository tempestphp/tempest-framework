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
         * Identifies the counter this limit is kept in. A named bucket is scoped to the client alone:
         * routes naming the same bucket share an allowance. Without a name, the limit gets its own
         * counter, scoped to what it was declared on.
         */
        public ?string $bucket = null,
    ) {}

    public function resolveLimits(Request $request, Container $container): array
    {
        return [$this->toRateLimit()];
    }

    /**
     * Returns the rate limit described by this attribute.
     */
    public function toRateLimit(): RateLimit
    {
        return new RateLimit(
            attempts: $this->attempts,
            window: $this->per->toDuration($this->every),
            key: $this->bucket,
        );
    }
}
