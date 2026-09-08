<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Attribute;
use Tempest\Container\Container;
use Tempest\Http\Request;

/**
 * Limits how often a route may be requested, using limits a {@see RateLimitProfile} resolves from the
 * request itself. Used over {@see Throttle} when the allowance is not a constant, such as when
 * authenticated clients get more of it.
 *
 * ```php
 * #[ThrottleWith(ApiRateLimitProfile::class)]
 * #[Get('/api/posts')]
 * public function index(): Response { /* … *\/ }
 * ```
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class ThrottleWith implements Throttles
{
    use AddsThrottleMiddleware;

    /**
     * A profile names its own counters, so it groups nothing here.
     */
    public ?string $bucket;

    public function __construct(
        /**
         * The profile resolving the limits that apply to a request.
         *
         * @var class-string<RateLimitProfile>
         */
        public string $profile,
    ) {
        $this->bucket = null;
    }

    public function resolveLimits(Request $request, Container $container): array
    {
        return $container->get($this->profile)->resolve($request);
    }
}
