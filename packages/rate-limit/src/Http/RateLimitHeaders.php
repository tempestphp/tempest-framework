<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\RateLimit\Config\RateLimitConfig;
use Tempest\RateLimit\RateLimitResult;

/**
 * The headers describing a rate limit to the client it applies to.
 */
final readonly class RateLimitHeaders
{
    /**
     * Returns the headers describing the specified result. Only a rejected result carries
     * `Retry-After`, which is sent even when the allowance headers are disabled, as a client
     * otherwise has no way of knowing when to come back.
     *
     * @return array<string, string>
     */
    public static function for(RateLimitResult $result, RateLimitConfig $config): array
    {
        $headers = $result->exceeded
            ? ['Retry-After' => (string) $result->retryAfterInSeconds]
            : [];

        if (! $config->includeHeaders) {
            return $headers;
        }

        return [
            ...$headers,
            'X-RateLimit-Limit' => (string) $result->limit,
            'X-RateLimit-Remaining' => (string) $result->remaining,
            'X-RateLimit-Reset' => (string) $result->resetsAtInSeconds,
        ];
    }
}
