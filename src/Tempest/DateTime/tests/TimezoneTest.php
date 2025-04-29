<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Timestamp;
use Tempest\DateTime\Timezone;

final class TimezoneTest extends TestCase
{
    use DateTimeTestTrait;

    public function test_default(): void
    {
        /**
         * @see DateTimeTestTrait::setUp() for the default timezone set to Europe/London
         */
        $this->assertSame(Timezone::EUROPE_LONDON, Timezone::default());
    }

    public function test_get_offset(): void
    {
        $temporal = Timestamp::fromParts(seconds: 1716956903);

        $this->assertSame(3600., Timezone::EUROPE_LONDON->getOffset($temporal)->getTotalSeconds());
        $this->assertSame(-14400., Timezone::AMERICA_NEW_YORK->getOffset($temporal)->getTotalSeconds());
        $this->assertSame(28800., Timezone::ASIA_SHANGHAI->getOffset($temporal)->getTotalSeconds());
        $this->assertSame(12600., Timezone::PLUS_0330->getOffset($temporal)->getTotalSeconds());
        $this->assertSame(-12600., Timezone::MINUS_0330->getOffset($temporal)->getTotalSeconds());
        $this->assertSame(3600., Timezone::PLUS_0100->getOffset($temporal)->getTotalSeconds());
        $this->assertSame(-3600., Timezone::MINUS_0100->getOffset($temporal)->getTotalSeconds());

        // Local
        $brussels = Timezone::EUROPE_BRUSSELS;
        date_default_timezone_set($brussels->value);

        $summer = DateTime::fromParts($brussels, 2024, 3, 31, 3);

        $this->assertSame(2., $brussels->getOffset($summer)->getTotalHours());
        $this->assertSame(1., $brussels->getOffset($summer, local: true)->getTotalHours());
    }

    #[TestWith([Timezone::EUROPE_LONDON, 0])]
    #[TestWith([Timezone::AMERICA_NEW_YORK, -18000])]
    #[TestWith([Timezone::ASIA_SHANGHAI, 28800])]
    public function test_raw_offset(Timezone $timezone, int $expected): void
    {
        $this->assertSame($expected, (int) $timezone->getRawOffset()->getTotalSeconds());
    }

    public function test_uses_daylight_saving_time(): void
    {
        $this->assertTrue(Timezone::AMERICA_NEW_YORK->usesDaylightSavingTime());
        $this->assertTrue(Timezone::EUROPE_LONDON->usesDaylightSavingTime());
        $this->assertFalse(Timezone::ASIA_SHANGHAI->usesDaylightSavingTime());
    }

    public function test_get_daylight_saving_time_savings(): void
    {
        $this->assertSame(3600., Timezone::AMERICA_NEW_YORK->getDaylightSavingTimeSavings()->getTotalSeconds());
        $this->assertSame(3600., Timezone::EUROPE_LONDON->getDaylightSavingTimeSavings()->getTotalSeconds());
        $this->assertSame(0., Timezone::ASIA_SHANGHAI->getDaylightSavingTimeSavings()->getTotalSeconds());
    }

    public function test_has_the_same_rules_as(): void
    {
        $this->assertTrue(Timezone::AMERICA_NEW_YORK->hasTheSameRulesAs(Timezone::AMERICA_NEW_YORK));
        $this->assertFalse(Timezone::AMERICA_NEW_YORK->hasTheSameRulesAs(Timezone::EUROPE_LONDON));
    }

    public function test_get_daylight_saving_time_offset(): void
    {
        $brussels = Timezone::EUROPE_BRUSSELS;
        date_default_timezone_set($brussels->value);

        $summer = DateTime::fromParts($brussels, 2024, 3, 31, 3);
        $winter = DateTime::fromParts($brussels, 2024, 10, 27, 2);

        $this->assertSame(0., $brussels->getDaylightSavingTimeOffset($winter)->getTotalHours());
        $this->assertSame(1., $brussels->getDaylightSavingTimeOffset($winter, local: true)->getTotalHours());
        $this->assertSame(1., $brussels->getDaylightSavingTimeOffset($summer)->getTotalHours());
        $this->assertSame(0., $brussels->getDaylightSavingTimeOffset($summer, local: true)->getTotalHours());
    }
}
