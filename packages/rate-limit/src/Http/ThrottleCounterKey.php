<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\RateLimit\RateLimit;
use Tempest\Router\MatchedRoute;

/**
 * Decides which counter a throttled request is spent from. The shape of these keys is not part of
 * the public API. To address a counter directly, give the limit a bucket and consume it through
 * {@see \Tempest\RateLimit\RateLimiter}.
 */
final readonly class ThrottleCounterKey
{
    public static function for(RateLimit $limit, MatchedRoute $matchedRoute, ThrottleScope $scope, ?string $client): string
    {
        // Identified clients are prefixed. This way, a resolver returning the string
        // `unidentified` still gets its own counter rather than the shared one.
        $client = $client === null ? 'unidentified' : 'client:' . $client;

        // A named bucket is scoped to the client alone: routes naming it spend from one allowance.
        if ($limit->key !== null) {
            return implode(':', ['bucket', $limit->key, $client]);
        }

        return implode(':', [
            ...self::scope($matchedRoute, $scope),
            self::allowance($limit),
            $client,
        ]);
    }

    /**
     * Returns what the limit is counted against, on top of the client.
     *
     * @return string[]
     */
    private static function scope(MatchedRoute $matchedRoute, ThrottleScope $scope): array
    {
        $handler = $matchedRoute->route->handler;

        return match ($scope) {
            ThrottleScope::CONTROLLER => [
                $handler->getDeclaringClass()->getName(),
                $scope->value,
            ],
            ThrottleScope::ROUTE => [
                $handler->getDeclaringClass()->getName(),
                $handler->getName(),
                $matchedRoute->route->uri,
                $scope->value,
            ],
        };
    }

    /**
     * Tells an unnamed limit apart from the ones declared alongside it. The allowance is used rather
     * than the declaration order: inserting an attribute leaves existing counters in place, and limits
     * describing the same allowance land in the same counter, as they are one limit, not two.
     */
    private static function allowance(RateLimit $limit): string
    {
        return "{$limit->attempts}_{$limit->window->getTotalSeconds()}";
    }
}
