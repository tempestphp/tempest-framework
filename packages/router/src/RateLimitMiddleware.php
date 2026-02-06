<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Cache\RateLimiting\RateLimiter;
use Tempest\Cache\RateLimiting\RateLimitResult;
use Tempest\Container\Container;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\TooManyRequests;
use Tempest\Http\Session\Session;

/**
 * Middleware that enforces rate limiting on routes decorated with #[RateLimit].
 *
 * Rate limit headers are added to all responses:
 * - X-RateLimit-Limit: Maximum requests allowed
 * - X-RateLimit-Remaining: Requests remaining in window
 * - X-RateLimit-Reset: Unix timestamp when limit resets
 *
 * When rate limit is exceeded, returns 429 Too Many Requests with Retry-After header.
 *
 * This middleware uses #[SkipDiscovery] to prevent auto-registration as a global middleware.
 * It should only run on routes that have the #[RateLimit] attribute.
 */
#[SkipDiscovery]
final readonly class RateLimitMiddleware implements HttpMiddleware
{
    public function __construct(
        private MatchedRoute $matchedRoute,
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $rateLimit = $this->getRateLimitAttribute();

        if ($rateLimit === null) {
            return $next($request);
        }

        // Get the rate limiter from container at invocation time to support testing
        $rateLimiter = $this->container->get(RateLimiter::class);

        $key = $this->resolveKey($rateLimit, $request);
        $result = $rateLimiter->attempt($key, $rateLimit->maxAttempts, $rateLimit->decaySeconds);

        if (! $result->allowed) {
            return $this->buildTooManyRequestsResponse($result);
        }

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $result);
    }

    /**
     * Get the RateLimit attribute from the current route handler.
     */
    private function getRateLimitAttribute(): ?RateLimit
    {
        $handler = $this->matchedRoute->route->handler;

        // Check method first, then class
        $rateLimit = $handler->getAttribute(RateLimit::class) ?? $handler->getDeclaringClass()->getAttribute(RateLimit::class);

        return $rateLimit;
    }

    /**
     * Resolve the rate limit key based on the configuration.
     */
    private function resolveKey(RateLimit $rateLimit, Request $request): string
    {
        $prefix = $rateLimit->key ?? $this->matchedRoute->route->uri;
        $identifier = $this->resolveIdentifier($rateLimit->by, $request);

        return sprintf('%s:%s', $prefix, $identifier);
    }

    /**
     * Resolve the client identifier based on the rate limit strategy.
     */
    private function resolveIdentifier(RateLimitBy|string $by, Request $request): string
    {
        // Handle custom resolver class
        if (is_string($by)) {
            /** @var RateLimitIdentifierResolver $resolver */
            $resolver = $this->container->get($by);

            return $resolver->resolve($request);
        }

        return match ($by) {
            RateLimitBy::USER => $this->resolveUserIdentifier($request),
            RateLimitBy::SESSION => $this->resolveSessionIdentifier($request),
            RateLimitBy::IP => $request->getClientIp(),
        };
    }

    /**
     * Resolve the authenticated user ID, falling back to IP if not authenticated.
     */
    private function resolveUserIdentifier(Request $request): string
    {
        if (! $this->container->has(Authenticator::class)) {
            return $request->getClientIp();
        }

        /** @var Authenticator $authenticator */
        $authenticator = $this->container->get(Authenticator::class);
        $user = $authenticator->current();

        if ($user === null) {
            return $request->getClientIp();
        }

        // Try to get an identifier from the authenticatable
        // Fall back to IP if the user doesn't have an id property
        if (property_exists($user, 'id')) {
            return 'user:' . (string) $user->id;
        }

        return $request->getClientIp();
    }

    /**
     * Resolve the session ID, falling back to IP if no session is available.
     */
    private function resolveSessionIdentifier(Request $request): string
    {
        if (! $this->container->has(Session::class)) {
            return $request->getClientIp();
        }

        $session = $this->container->get(Session::class);

        return 'session:' . $session->id;
    }

    /**
     * Build a 429 Too Many Requests response.
     */
    private function buildTooManyRequestsResponse(RateLimitResult $result): TooManyRequests
    {
        return new TooManyRequests(
            retryAfter: $result->retryAfter,
            limit: $result->limit,
            remaining: $result->remaining,
            resetAt: $result->resetAt,
        );
    }

    /**
     * Add rate limit headers to the response.
     */
    private function addRateLimitHeaders(Response $response, RateLimitResult $result): Response
    {
        return $response
            ->addHeader('X-RateLimit-Limit', (string) $result->limit)
            ->addHeader('X-RateLimit-Remaining', (string) $result->remaining)
            ->addHeader('X-RateLimit-Reset', (string) $result->resetAt);
    }
}
