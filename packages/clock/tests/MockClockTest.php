<?php

declare(strict_types=1);

namespace Tempest\Clock\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tempest\Clock\MockClock;
use Tempest\DateTime\DateTime;

/**
 * @internal
 */
final class MockClockTest extends TestCase
{
    public function test_mock_clock_returns_the_date_time_we_want(): void
    {
        $time = new DateTimeImmutable('2024-09-11 13:54:23');
        $clock = new MockClock($time);

        $this->assertEquals($time, $clock->now()->toNativeDateTime());
    }

    public function test_mock_clock_defaults_to_now(): void
    {
        // Because it is tough to test this without mocking, we just make
        // sure there is no variance greater than one second.
        $clock = new MockClock();

        $beforeDateTime = new DateTimeImmutable('now');
        $clockDateTime = $clock->now();
        $afterDateTime = new DateTimeImmutable('now');

        $this->assertGreaterThanOrEqual($beforeDateTime->getTimestamp(), $clockDateTime->getTimestamp()->getSeconds());
        $this->assertLessThanOrEqual($afterDateTime->getTimestamp(), $clockDateTime->getTimestamp()->getSeconds());
    }

    public function test_mock_clock_returns_the_time_we_want(): void
    {
        $time = DateTime::parse('2024-09-11 13:54:23');
        $clock = new MockClock($time);

        $this->assertEquals($time->getTimestamp()->getSeconds(), $clock->seconds());
    }

    public function test_mock_clock_sleeps_time(): void
    {
        $oldTime = DateTime::parse('2024-09-11 13:54:23');
        $expectedTime = DateTime::parse('2024-09-11 13:54:25');

        $clock = new MockClock($oldTime);
        $clock->sleep(2_000);

        $this->assertSame($expectedTime->getTimestamp()->getSeconds(), $clock->seconds());
    }

    public function test_mock_clock_can_change_time(): void
    {
        $dateTime = DateTime::parse('2024-09-11 13:54:23');
        $subtractedTime = DateTime::parse('2024-09-11 13:54:21');
        $addedTime = DateTime::parse('2024-09-11 13:54:25');
        $clock = new MockClock($dateTime);

        $clock->minus(2);

        $this->assertEquals($subtractedTime, $clock->now());
        $this->assertEquals($subtractedTime->getTimestamp()->getSeconds(), $clock->seconds());

        $clock->plus(4);

        $this->assertEquals($addedTime, $clock->now());
        $this->assertEquals($addedTime->getTimestamp()->getSeconds(), $clock->seconds());
    }
}
