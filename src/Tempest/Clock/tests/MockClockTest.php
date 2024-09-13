<?php

declare(strict_types=1);

namespace Tempest\Clock\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tempest\Clock\MockClock;

/**
 * @internal
 * @small
 */
final class MockClockTest extends TestCase
{
    public function test_mock_clock_returns_the_date_time_we_want(): void
    {
        $time = new DateTimeImmutable('2024-09-11 13:54:23');
        $clock = new MockClock($time);

        $this->assertEquals($time, $clock->now());
    }

    public function test_mock_clock_defaults_to_now(): void
    {
        // Because it is tough to test this without mocking, we just make
        // sure there is no variance greater than one second.
        $clock = new MockClock();

        $beforeDateTime = new DateTimeImmutable('now');
        $clockDateTime = $clock->now();
        $afterDateTime = new DateTimeImmutable('now');

        $this->assertGreaterThanOrEqual($beforeDateTime->getTimestamp(), $clockDateTime->getTimestamp());
        $this->assertLessThanOrEqual($afterDateTime->getTimestamp(), $clockDateTime->getTimestamp());
    }

    public function test_mock_clock_returns_the_time_we_want(): void
    {
        $time = new DateTimeImmutable('2024-09-11 13:54:23');
        $clock = new MockClock($time);

        $this->assertEquals($time->getTimestamp(), $clock->time());
    }

    public function test_mock_clock_sleeps_time(): void
    {
        $oldTime = new DateTimeImmutable('2024-09-11 13:54:23');
        $expectedTime = new DateTimeImmutable('2024-09-11 13:54:25');

        $clock = new MockClock($oldTime);
        $clock->sleep(2);

        $this->assertSame($expectedTime->getTimestamp(), $clock->time());
    }

    public function test_mock_clock_can_change_time(): void
    {
        $dateTime = new DateTimeImmutable('2024-09-11 13:54:23');
        $subtractedTime = new DateTimeImmutable('2024-09-11 13:54:21');
        $addedTime = new DateTimeImmutable('2024-09-11 13:54:25');
        $clock = new MockClock($dateTime);

        $clock->changeTime(-2);

        $this->assertEquals($subtractedTime, $clock->now());
        $this->assertEquals($subtractedTime->getTimestamp(), $clock->time());

        $clock->changeTime(4);

        $this->assertEquals($addedTime, $clock->now());
        $this->assertEquals($addedTime->getTimestamp(), $clock->time());
    }
}
