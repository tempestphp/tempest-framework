<?php

declare(strict_types=1);

namespace  Tests\Tempest\Console\Scheduler;

use Tempest\Console\Scheduler\Interval;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
final class IntervalTest extends TestCase
{
    public function test_interval_with_seconds_returns_correct_in_seconds()
    {
        $interval = new Interval(seconds: 1);

        $this->assertSame(1, $interval->inSeconds());
    }

    public function test_interval_with_minutes_returns_correct_in_seconds()
    {
        $interval = new Interval(minutes: 1, seconds: 1);

        $this->assertSame(60 + 1, $interval->inSeconds());
    }

    public function test_interval_with_hours_returns_correct_in_seconds()
    {
        $interval = new Interval(hours: 1, minutes: 1, seconds: 1);

        $this->assertSame(60 * 60 + 1 * 60 + 1, $interval->inSeconds());
    }

    public function test_interval_with_days_returns_correct_in_seconds()
    {
        $interval = new Interval(days: 1, hours: 1, minutes: 1, seconds: 1);

        $this->assertSame(24 * 60 * 60 + 1 * 60 * 60 + 1 * 60 + 1, $interval->inSeconds());
    }

    public function test_interval_with_weeks_returns_correct_in_seconds()
    {
        $interval = new Interval(weeks: 1, days: 1, hours: 1, minutes: 1, seconds: 1);

        $this->assertSame(7 * 24 * 60 * 60 + 1 * 24 * 60 * 60 + 1 * 60 * 60 + 1 * 60 + 1, $interval->inSeconds());
    }

    public function test_interval_with_months_returns_correct_in_seconds()
    {
        $interval = new Interval(months: 1, weeks: 1, days: 1, hours: 1, minutes: 1, seconds: 1);

        $this->assertSame(30 * 24 * 60 * 60 + 7 * 24 * 60 * 60 + 1 * 24 * 60 * 60 + 1 * 60 * 60 + 1 * 60 + 1, $interval->inSeconds());
    }

    public function test_interval_with_years_returns_correct_in_seconds()
    {
        $interval = new Interval(years: 1, months: 1, weeks: 1, days: 1, hours: 1, minutes: 1, seconds: 1);

        $this->assertSame(365 * 24 * 60 * 60 + 30 * 24 * 60 * 60 + 7 * 24 * 60 * 60 + 1 * 24 * 60 * 60 + 1 * 60 * 60 + 1 * 60 + 1, $interval->inSeconds());
    }
}
