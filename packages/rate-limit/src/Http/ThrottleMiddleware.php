<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\Container\Container;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\RateLimit\Config\RateLimitConfig;
use Tempest\RateLimit\RateLimit;
use Tempest\RateLimit\RateLimiter;
use Tempest\RateLimit\RateLimitResult;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\MatchedRoute;

/**
 * Applies the limits declared by {@see Throttle} and {@see ThrottleWith} to the matched route. This
 * middleware is not discovered globally. It is added to a route by the attributes themselves.
 */
#[SkipDiscovery]
final readonly class ThrottleMiddleware implements HttpMiddleware
{
    public function __construct(
        private RateLimiter $limiter,
        private RateLimitKeyResolver $keyResolver,
        private RateLimitConfig $config,
        private MatchedRoute $matchedRoute,
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if (! $this->config->enabled) {
            return $next($request);
        }

        $limits = $this->resolveLimits($request);

        if ($limits === []) {
            return $next($request);
        }

        $results = [];

        // The first rejection stops the rest. A request turned away by a narrow window does not
        // also spend the wider allowances behind it.
        foreach ($limits as $limit) {
            $result = $this->limiter->attempt($limit);

            if ($result->exceeded) {
                $this->reject($result);
            }

            $results[] = $result;
        }

        $response = $next($request);

        foreach (RateLimitHeaders::for($this->mostConstrained(...$results), $this->config) as $name => $value) {
            $response->addHeader($name, $value);
        }

        return $response;
    }

    /**
     * @return RateLimit[]
     */
    private function resolveLimits(Request $request): array
    {
        // Resolving a client may be more than reading an address. It's done once for all limits.
        $client = $this->keyResolver->resolve($request);
        $limits = [];

        foreach ($this->resolveAttributes() as $scope => $throttles) {
            foreach ($throttles as $throttle) {
                foreach ($throttle->resolveLimits($request, $this->container) as $limit) {
                    $key = ThrottleCounterKey::for($limit, $this->matchedRoute, ThrottleScope::from($scope), $client);

                    // Limits landing in the same counter describe one allowance: declaring the
                    // same limit twice throttles a route exactly once.
                    $limits[$key] = $limit->withKey($key);
                }
            }
        }

        $limits = array_values($limits);

        // Narrow windows are consumed first, this way requests rejected by a per-minute limit
        // leave the daily allowance untouched. It also keeps the outcome independent of the
        // order the attributes were declared in.
        usort($limits, fn (RateLimit $a, RateLimit $b) => $a->window->getTotalSeconds() <=> $b->window->getTotalSeconds());

        return $limits;
    }

    /**
     * Returns the throttling attributes declared on the route and on its controller. The route's own
     * limits come first. A request rejected by one route then leaves the allowance it shares with its
     * siblings intact. Sorting is stable, and {@see self::resolveLimits()} preserves that order.
     *
     * @return array<string, Throttles[]>
     */
    private function resolveAttributes(): array
    {
        $handler = $this->matchedRoute->route->handler;

        return array_filter([
            ThrottleScope::ROUTE->value => $handler->getAttributes(Throttles::class),
            ThrottleScope::CONTROLLER->value => $handler->getDeclaringClass()->getAttributes(Throttles::class),
        ]);
    }

    private function mostConstrained(RateLimitResult $result, RateLimitResult ...$others): RateLimitResult
    {
        return array_reduce(
            array: $others,
            callback: fn (RateLimitResult $carry, RateLimitResult $other) => $other->remaining < $carry->remaining ? $other : $carry,
            initial: $result,
        );
    }

    /**
     * Rejects the request. Error responses are rendered from scratch. Headers set on a response
     * would be discarded.
     */
    private function reject(RateLimitResult $result): never
    {
        throw new HttpRequestFailed(
            status: Status::TOO_MANY_REQUESTS,
            headers: RateLimitHeaders::for($result, $this->config),
        );
    }
}
