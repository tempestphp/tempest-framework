<?php

declare(strict_types=1);

namespace Tempest\Clock;

enum TimeUnit: string
{
    case MICROSECOND = 'microsecond';
    case MILLISECOND = 'millisecond';
    case SECOND = 'second';
    case MINUTE = 'minute';
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public function toMicroseconds(): int
    {
        return match ($this) {
            self::MICROSECOND => 1,
            self::MILLISECOND => 1_000,
            self::SECOND => 1_000_000,
            self::MINUTE => 60 * 1_000_000,
            self::HOUR => 60 * 60 * 1_000_000,
            self::DAY => 24 * 60 * 60 * 1_000_000,
            self::WEEK => 7 * 24 * 60 * 60 * 1_000_000,
            self::MONTH => 30 * 24 * 60 * 60 * 1_000_000,
            self::YEAR => 365 * 24 * 60 * 60 * 1_000_000,
        };
    }
}
