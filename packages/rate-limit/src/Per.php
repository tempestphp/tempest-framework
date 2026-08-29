<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Tempest\DateTime\Duration;

/**
 * The unit of time a rate limit window is expressed in.
 */
enum Per
{
    case SECOND;
    case MINUTE;
    case HOUR;
    case DAY;

    public function toDuration(int $count = 1): Duration
    {
        return match ($this) {
            self::SECOND => Duration::seconds($count),
            self::MINUTE => Duration::minutes($count),
            self::HOUR => Duration::hours($count),
            self::DAY => Duration::days($count),
        };
    }
}
