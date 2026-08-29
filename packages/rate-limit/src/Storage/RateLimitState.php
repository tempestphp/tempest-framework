<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Storage;

use Tempest\Clock\Clock;
use Tempest\DateTime\Duration;

/**
 * The persisted state of a single rate limit window.
 */
final readonly class RateLimitState
{
    public function __construct(
        /**
         * The amount of attempts made within the window.
         */
        public int $hits,

        /**
         * The UNIX timestamp, in seconds, at which the window ends.
         */
        public int $resetsAtInSeconds,
    ) {}

    /**
     * Opens a window of the specified duration, starting now.
     */
    public static function opening(Clock $clock, Duration $window, int $hits = 0): self
    {
        return new self(
            hits: $hits,
            resetsAtInSeconds: $clock->seconds() + self::windowInSeconds($window),
        );
    }

    /**
     * Returns the length of the specified window, in seconds. Expiration is second-granular:
     * one second is the shortest window that can be honored.
     */
    public static function windowInSeconds(Duration $window): int
    {
        return max(1, (int) ceil($window->getTotalSeconds()));
    }

    /**
     * Records attempts within the current window, leaving its end untouched.
     */
    public function incrementedBy(int $by): self
    {
        return new self(
            hits: $this->hits + $by,
            resetsAtInSeconds: $this->resetsAtInSeconds,
        );
    }
}
