<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

enum Every
{
    case Second;
    case Minute;
    case QuarterHour;
    case HalfHour;
    case Hour;
    case HalfDay;
    case Day;
    case Week;
    case Month;
    case Year;

    public function toInterval(): Interval
    {
        return match ($this) {
            self::Second => new Interval(seconds: 1),
            self::Minute => new Interval(minutes: 1),
            self::QuarterHour => new Interval(minutes: 15),
            self::HalfHour => new Interval(minutes: 30),
            self::Hour => new Interval(hours: 1),
            self::HalfDay => new Interval(hours: 12),
            self::Day => new Interval(days: 1),
            self::Week => new Interval(weeks: 1),
            self::Month => new Interval(months: 1),
            self::Year => new Interval(years: 1),
        };
    }
}
