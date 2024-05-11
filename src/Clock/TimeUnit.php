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
            self::MINUTE => 60_000_000,
            self::HOUR => 3_600_000_000,
            self::DAY => 86_400_000_000,
            self::WEEK => 604_800_000_000,
            self::MONTH => 2_592_000_000_000,
            self::YEAR => 31_536_000_000_000,
        };
    }
}
