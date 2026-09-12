<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Attribute;
use InvalidArgumentException;
use Tempest\Container\Container;
use Tempest\Http\Request;
use Tempest\RateLimit\Per;
use Tempest\RateLimit\RateLimit;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

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
 * When the limits depend on the request itself, provide a {@see RateLimitProfile} instead of
 * `attempts`.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Throttle implements RouteDecorator
{
    public function __construct(
        /**
         * The maximum amount of requests allowed within the window.
         */
        public ?int $attempts = null,

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

        /**
         * Resolves limits dynamically from the request. This cannot be combined with a static limit.
         *
         * @var class-string<RateLimitProfile>|null
         */
        public ?string $profile = null,
    ) {
        if ($profile !== null && ($attempts !== null || $per !== Per::MINUTE || $every !== 1 || $bucket !== null)) {
            throw new InvalidArgumentException('A rate limit profile cannot be combined with attempts, per, every, or bucket.');
        }

        if ($profile === null && $attempts === null) {
            throw new InvalidArgumentException('A rate limit must provide either attempts or a profile.');
        }

        if ($attempts !== null && $attempts < 1) {
            throw new InvalidArgumentException('Rate limit attempts must be greater than zero.');
        }

        if ($every < 1) {
            throw new InvalidArgumentException('Rate limit every must be greater than zero.');
        }
    }

    public function resolveLimits(Request $request, Container $container): array
    {
        if ($this->profile !== null) {
            return $container->get($this->profile)->resolve($request);
        }

        return [$this->toRateLimit()];
    }

    public function decorate(Route $route): Route
    {
        if (in_array(ThrottleMiddleware::class, $route->middleware, strict: true)) {
            return $route;
        }

        $route->middleware = [
            ...$route->middleware,
            ThrottleMiddleware::class,
        ];

        return $route;
    }

    /**
     * Returns the rate limit described by this attribute. The bucket is not part of it: it groups
     * routes rather than naming a counter, and is applied by {@see ThrottleCounterKey}.
     */
    public function toRateLimit(): RateLimit
    {
        if ($this->attempts === null) {
            throw new InvalidArgumentException('A rate limit profile cannot be converted to a static rate limit.');
        }

        return new RateLimit(
            attempts: $this->attempts,
            window: $this->per->toDuration($this->every),
        );
    }
}
