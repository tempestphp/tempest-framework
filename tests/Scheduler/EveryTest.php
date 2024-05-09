<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

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
        $every = Every::SECOND;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->seconds);
        $this->assertSame(1, $interval->inSeconds());
    }

    public function test_every_minute_gets_transformed_to_interval()
    {
        $every = Every::MINUTE;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->minutes);
        $this->assertSame(60, $interval->inSeconds());
    }

    public function test_every_quarter_hour_gets_transformed_to_interval()
    {
        $every = Every::QUARTER;
        $interval = $every->toInterval();

        $this->assertSame(15, $interval->minutes);
        $this->assertSame(15 * 60, $interval->inSeconds());
    }

    public function test_every_half_hour_gets_transformed_to_interval()
    {
        $every = Every::HALF_HOUR;
        $interval = $every->toInterval();

        $this->assertSame(30, $interval->minutes);
        $this->assertSame(30 * 60, $interval->inSeconds());
    }

    public function test_every_hour_gets_transformed_to_interval()
    {
        $every = Every::HOUR;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->hours);
        $this->assertSame(60 * 60, $interval->inSeconds());
    }

    public function test_every_half_day_gets_transformed_to_interval()
    {
        $every = Every::HALF_DAY;
        $interval = $every->toInterval();

        $this->assertSame(12, $interval->hours);
        $this->assertSame(12 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_day_gets_transformed_to_interval()
    {
        $every = Every::DAY;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->days);
        $this->assertSame(24 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_week_gets_transformed_to_interval()
    {
        $every = Every::WEEK;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->weeks);
        $this->assertSame(7 * 24 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_month_gets_transformed_to_interval()
    {
        $every = Every::MONTH;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->months);
        $this->assertSame(30 * 24 * 60 * 60, $interval->inSeconds());
    }

    public function test_every_year_gets_transformed_to_interval()
    {
        $every = Every::YEAR;
        $interval = $every->toInterval();

        $this->assertSame(1, $interval->years);
        $this->assertSame(365 * 24 * 60 * 60, $interval->inSeconds());
    }
}
