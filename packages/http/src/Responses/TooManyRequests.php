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
        ?int $retryAfter = null,
        /** The maximum number of requests allowed in the time window. */
        ?int $limit = null,
        /** The number of requests remaining in the current time window. */
        ?int $remaining = null,
        /** The Unix timestamp when the rate limit resets. */
        ?int $resetAt = null,
    ) {
        $this->status = Status::TOO_MANY_REQUESTS;

        // Set body as array to ensure the original response is returned by exception handlers
        // when this response is wrapped in HttpRequestFailed (see JsonExceptionRenderer)
        $this->body = [
            'error' => 'Too Many Requests',
            'retry_after' => $retryAfter,
        ];

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
