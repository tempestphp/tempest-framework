<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

/**
 * The outcome of consuming or inspecting a {@see RateLimit}.
 */
final class RateLimitResult
{
    public function __construct(
        /**
         * The key the counter is stored under.
         */
        public readonly string $key,

        /**
         * Whether the attempt fits within the limit.
         */
        public readonly bool $allowed,

        /**
         * The maximum amount of attempts allowed within the window.
         */
        public readonly int $limit,

        /**
         * The amount of attempts made within the current window.
         */
        public readonly int $hits,

        /**
         * The UNIX timestamp, in seconds, at which the current window ends. A limit with no window
         * open reports the present moment.
         */
        public readonly int $resetsAtInSeconds,

        /**
         * The amount of seconds to wait before attempting again, or zero when the attempt was allowed.
         */
        public readonly int $retryAfterInSeconds,
    ) {}

    /**
     * Whether the limit was exceeded.
     */
    public bool $exceeded {
        get => ! $this->allowed;
    }

    /**
     * The amount of attempts left within the current window.
     */
    public int $remaining {
        get => max(0, $this->limit - $this->hits);
    }

    /**
     * The moment at which the current window ends and attempts become available again.
     */
    public DateTimeInterface $resetsAt {
        get => DateTime::fromTimestamp($this->resetsAtInSeconds);
    }

    /**
     * How long to wait before attempting again.
     */
    public Duration $retryAfter {
        get => Duration::seconds($this->retryAfterInSeconds);
    }
}
