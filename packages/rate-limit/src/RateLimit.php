<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Stringable;
use Tempest\DateTime\Duration;

/**
 * Describes how many attempts are allowed for a specific key within a window of time.
 */
final readonly class RateLimit
{
    public function __construct(
        /**
         * The maximum amount of attempts allowed within `$window`.
         */
        public int $attempts,

        /**
         * The amount of time during which attempts are counted.
         */
        public Duration $window,

        /**
         * Identifies what is being limited. Two rate limits with the same key share the same counter.
         */
        public ?string $key = null,
    ) {}

    public static function perSecond(int $attempts, int $seconds = 1): self
    {
        return new self($attempts, Per::SECOND->toDuration($seconds));
    }

    public static function perMinute(int $attempts, int $minutes = 1): self
    {
        return new self($attempts, Per::MINUTE->toDuration($minutes));
    }

    public static function perHour(int $attempts, int $hours = 1): self
    {
        return new self($attempts, Per::HOUR->toDuration($hours));
    }

    public static function perDay(int $attempts, int $days = 1): self
    {
        return new self($attempts, Per::DAY->toDuration($days));
    }

    /**
     * Returns a copy of this rate limit scoped to the specified key.
     */
    public function withKey(Stringable|string $key): self
    {
        return new self(
            attempts: $this->attempts,
            window: $this->window,
            key: (string) $key,
        );
    }

    /**
     * Returns a copy of this rate limit with the specified key appended to the current one.
     */
    public function scopedTo(Stringable|string $key): self
    {
        return $this->withKey($this->key === null ? (string) $key : $this->key . ':' . $key);
    }
}
