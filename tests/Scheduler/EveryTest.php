<?php

declare(strict_types=1);

namespace Scheduler;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Scheduler\Every;

/**
 * @internal
 * @small
 */
final class EveryTest extends TestCase
{
    public function test_every_second_gets_transformed_to_interval()
    {
        $every = Every::Second;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->seconds);
        $this->assertSame(1, $interval->inSeconds());
    }

    public function test_every_minute_gets_transformed_to_interval()
    {
        $every = Every::Minute;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->minutes);
        $this->assertSame(60, $interval->inSeconds());
    }

    public function test_every_quarter_hour_gets_transformed_to_interval()
    {
        $every = Every::QuarterHour;
        $interval = $every->toInterval();

        $this->assertSame(15, $interval->minutes);
        $this->assertSame(15 * 60, $interval->inSeconds());
    }

    public function test_every_half_hour_gets_transformed_to_interval()
    {
        $every = Every::HalfHour;
        $interval = $every->toInterval();

        $this->assertSame(30, $interval->minutes);
        $this->assertSame(30 * 60, $interval->inSeconds());
    }

    public function test_every_hour_gets_transformed_to_interval()
    {
        $every = Every::Hour;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->hours);
        $this->assertSame(60 * 60, $interval->inSeconds());
    }

    public function test_every_half_day_gets_transformed_to_interval()
    {
        $every = Every::HalfDay;
        $interval = $every->toInterval();

        $this->assertSame(12, $interval->hours);
        $this->assertSame(12 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_day_gets_transformed_to_interval()
    {
        $every = Every::Day;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->days);
        $this->assertSame(24 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_week_gets_transformed_to_interval()
    {
        $every = Every::Week;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->weeks);
        $this->assertSame(7 * 24 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_month_gets_transformed_to_interval()
    {
        $every = Every::Month;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->months);
        $this->assertSame(30 * 24 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_year_gets_transformed_to_interval()
    {
        $every = Every::Year;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->years);
        $this->assertSame(365 * 24 * 60 * 60, $interval->inSeconds());
    }
}
