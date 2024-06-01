<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

enum Every
{
    case MINUTE;
    case QUARTER;
    case HALF_HOUR;
    case HOUR;
    case HALF_DAY;
    case DAY;
    case WEEK;
    case MONTH;
    case YEAR;

    public function toInterval(): Interval
    {
        return match ($this) {
            self::MINUTE => new Interval(minutes: 1),
            self::QUARTER => new Interval(minutes: 15),
            self::HALF_HOUR => new Interval(minutes: 30),
            self::HOUR => new Interval(hours: 1),
            self::HALF_DAY => new Interval(hours: 12),
            self::DAY => new Interval(days: 1),
            self::WEEK => new Interval(weeks: 1),
            self::MONTH => new Interval(months: 1),
            self::YEAR => new Interval(years: 1),
        };
    }
}
