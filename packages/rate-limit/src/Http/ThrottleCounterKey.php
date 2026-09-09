<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\RateLimit\RateLimit;
use Tempest\Router\MatchedRoute;

/**
 * Decides which counter a throttled request is spent from. The shape of the keys derived here is not
 * part of the public API. To address a counter directly, give the limit a key of your own through a
 * {@see RateLimitProfile} and consume it through {@see \Tempest\RateLimit\RateLimiter}.
 */
final readonly class ThrottleCounterKey
{
    public static function for(RateLimit $limit, ?string $bucket, MatchedRoute $matchedRoute, ThrottleScope $scope, ?string $client): string
    {
        // A key the application built is the counter itself, and stays addressable through
        // `RateLimiter`. Scoping it would name a counter no caller could reach.
        if ($limit->key !== null) {
            return $limit->key;
        }

        // Identified clients are prefixed. This way, a resolver returning the string
        // `unidentified` still gets its own counter rather than the shared one.
        $client = $client === null ? 'unidentified' : 'client:' . $client;

        // A bucket groups routes rather than naming a counter, so it stays scoped to the client. The
        // window is part of the key, but the attempts are not: routes sharing a bucket spend from one
        // allowance, and the narrowest of them decides how much of it there is. Were the window left
        // out, limits measuring different spans would land in one counter, and whichever request
        // opened it would decide how long it lasts.
        if ($bucket !== null) {
            return implode(':', ['bucket', $bucket, self::window($limit), $client]);
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
            // The method is part of the scope: a handler answering both `GET` and `POST` on the
            // same URI exposes two routes, each with an allowance of its own.
            ThrottleScope::ROUTE => [
                $handler->getDeclaringClass()->getName(),
                $handler->getName(),
                $matchedRoute->route->method->value,
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
        return "{$limit->attempts}_" . self::window($limit);
    }

    /**
     * Returns the span the limit measures, in seconds.
     */
    private static function window(RateLimit $limit): string
    {
        return (string) $limit->window->getTotalSeconds();
    }
}
