<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Container\Container;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\TooManyRequests;
use Tempest\Http\Session\Session;
use Tempest\Router\RateLimiting\RateLimiter;
use Tempest\Router\RateLimiting\RateLimitResult;

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
 * Note: This class intentionally does NOT implement HttpMiddleware to prevent
 * it from being auto-discovered as a global middleware. It should only run
 * on routes that have the #[RateLimit] attribute, via HandleRouteSpecificMiddleware.
 */
final readonly class RateLimitMiddleware
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
    private function resolveIdentifier(string $by, Request $request): string
    {
        return match ($by) {
            'user' => $this->resolveUserIdentifier(),
            'session' => $this->resolveSessionIdentifier(),
            default => $this->resolveIpIdentifier($request),
        };
    }

    /**
     * Resolve the client IP address from the request.
     */
    private function resolveIpIdentifier(Request $request): string
    {
        // Check for proxy headers first
        $forwardedFor = $request->headers->get('X-Forwarded-For');

        if ($forwardedFor !== null) {
            // X-Forwarded-For can contain multiple IPs, the first one is the client
            $ips = explode(',', $forwardedFor);

            return trim($ips[0]);
        }

        $realIp = $request->headers->get('X-Real-IP');

        if ($realIp !== null) {
            return $realIp;
        }

        // Fall back to REMOTE_ADDR
        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    /**
     * Resolve the authenticated user ID.
     */
    private function resolveUserIdentifier(): string
    {
        if (! $this->container->has(Authenticator::class)) {
            return 'anonymous';
        }

        /** @var Authenticator $authenticator */
        $authenticator = $this->container->get(Authenticator::class);
        $user = $authenticator->current();

        if ($user === null) {
            return 'anonymous';
        }

        // Try to get an identifier from the authenticatable
        // Use the object's hash as a fallback if no id property exists
        if (property_exists($user, 'id')) {
            return 'user:' . (string) $user->id;
        }

        return 'user:' . spl_object_id($user);
    }

    /**
     * Resolve the session ID.
     */
    private function resolveSessionIdentifier(): string
    {
        if (! $this->container->has(Session::class)) {
            return 'no-session';
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
