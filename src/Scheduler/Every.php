<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

enum Every
{
    case Second;
    case Minute;
    case Hour;
    case Day;
    case Week;
    case Month;
    case Year;

    public function toInterval(): Interval
    {
        return match ($this) {
            self::Second => new Interval(seconds: 1),
            self::Minute => new Interval(minutes: 1),
            self::Hour => new Interval(hours: 1),
            self::Day => new Interval(days: 1),
            self::Week => new Interval(weeks: 1),
            self::Month => new Interval(months: 1),
            self::Year => new Interval(years: 1),
        };
    }
}
