<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

/**
 * An HTTP 429 Too Many Requests response.
 *
 * This response should be returned when a client has exceeded the rate limit.
 */
final class TooManyRequests implements Response
{
    use IsResponse;

    public function __construct(
        /** The number of seconds until the rate limit resets. */
        private(set) ?int $retryAfter = null,
        /** The maximum number of requests allowed in the time window. */
        private(set) ?int $limit = null,
        /** The number of requests remaining in the current time window. */
        private(set) ?int $remaining = null,
        /** The Unix timestamp when the rate limit resets. */
        private(set) ?int $resetAt = null,
    ) {
        $this->status = Status::TOO_MANY_REQUESTS;
        $this->body = 'Too Many Requests';

        if ($retryAfter !== null) {
            $this->addHeader('Retry-After', (string) $retryAfter);
        }

        if ($limit !== null) {
            $this->addHeader('X-RateLimit-Limit', (string) $limit);
        }

        if ($remaining !== null) {
            $this->addHeader('X-RateLimit-Remaining', (string) $remaining);
        }

        if ($resetAt !== null) {
            $this->addHeader('X-RateLimit-Reset', (string) $resetAt);
        }
    }
}
