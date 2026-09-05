<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\Http\Request;

/**
 * Counts requests by client IP. Requires {@see \Tempest\Http\Ip\TrustedProxiesConfig}
 * for reliable client IPs behind proxies.
 */
final readonly class ClientIPKeyResolver implements RateLimitKeyResolver
{
    public function resolve(Request $request): ?string
    {
        // Keyed by packed bytes: `::ffff:127.0.0.1` and `127.0.0.1` share a counter.
        return $request->ip === null
            ? null
            : bin2hex($request->ip->bytes);
    }
}
