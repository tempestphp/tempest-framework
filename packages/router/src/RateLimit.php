<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;

/**
 * Apply rate limiting to a route or controller.
 *
 * Rate limiting protects your API from abuse by limiting how many requests
 * a client can make within a time window.
 *
 * ```php
 * #[Get('/api/users')]
 * #[RateLimit(maxAttempts: 60, decaySeconds: 60)]
 * public function index(): Response
 * {
 *     // This route allows 60 requests per minute per client
 * }
 * ```
 *
 * When applied to a class, all routes in that controller will be rate limited:
 *
 * ```php
 * #[RateLimit(maxAttempts: 100, decaySeconds: 60)]
 * class ApiController
 * {
 *     // All routes in this controller are rate limited
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class RateLimit implements RouteDecorator
{
    public function __construct(
        /** The maximum number of requests allowed within the time window. */
        public int $maxAttempts = 60,
        /** The time window in seconds after which the rate limit resets. */
        public int $decaySeconds = 60,
        /**
         * A unique key prefix for this rate limit.
         * Use this to create separate limits for different resources.
         * Defaults to the route URI.
         */
        public ?string $key = null,
        /**
         * How to resolve the client identifier.
         * - 'ip': Use client IP address (default)
         * - 'user': Use authenticated user ID
         * - 'session': Use session ID
         *
         * @var 'ip'|'user'|'session'
         */
        public string $by = 'ip',
    ) {}

    public function decorate(Route $route): Route
    {
        // RateLimitMiddleware intentionally doesn't implement HttpMiddleware to prevent
        // auto-discovery as a global middleware. It follows the same callable signature
        // and is invoked via HandleRouteSpecificMiddleware.
        $route->middleware = [
            ...$route->middleware,
            RateLimitMiddleware::class,
        ];

        return $route;
    }
}
