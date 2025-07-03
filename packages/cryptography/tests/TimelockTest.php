<?php

namespace Tempest\Cryptography\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Clock\Clock;
use Tempest\Clock\GenericClock;
use Tempest\Clock\MockClock;
use Tempest\Cryptography\Timelock;
use Tempest\DateTime\Duration;

final class TimelockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! interface_exists(Clock::class)) {
            $this->markTestSkipped('The Clock interface is not available. This test requires the `tempest/clock` package.');
        }
    }

    public function test_callback_is_executed(): void
    {
        $clock = new GenericClock();
        $result = new Timelock($clock)->invoke(fn () => 'hello', Duration::zero());

        $this->assertSame('hello', $result);
    }

    public function test_locks_for_duration(): void
    {
        $clock = new GenericClock();
        $start = microtime(true);

        $timelock = new Timelock($clock);
        $timelock->invoke(fn () => null, Duration::milliseconds(100));

        $elapsed = microtime(true) - $start;

        $this->assertGreaterThanOrEqual(0.1, $elapsed, 'The timelock did not wait for the specified duration.');
        $this->assertLessThan(0.2, $elapsed, 'The timelock waited for too long.');
    }

    public function test_return_early(): void
    {
        $clock = new GenericClock();
        $timelock = new Timelock($clock);

        $start = microtime(true);
        $timelock->invoke(
            callback: fn (Timelock $lock) => $lock->canReturnEarly = true,
            duration: Duration::milliseconds(100),
        );
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(0.1, $elapsed, 'The timelock did not return early as expected.');
    }

    public function test_throws_exception_after_delay(): void
    {
        $clock = new GenericClock();
        $timelock = new Timelock($clock);

        $start = microtime(true);

        try {
            $timelock->invoke(
                callback: fn () => throw new \RuntimeException('This is an error.'),
                duration: Duration::milliseconds(100),
            );
        } catch (\RuntimeException) {
            $elapsed = microtime(true) - $start;
            $this->assertGreaterThanOrEqual(0.1, $elapsed, 'The exception was thrown before the timelock duration elapsed.');
        }
    }

    public function test_uses_clock_to_sleep(): void
    {
        $clock = new MockClock();
        $timelock = new Timelock($clock);

        $ms = $clock->timestamp()->getMilliseconds();

        $timelock->invoke(
            callback: fn () => null,
            duration: Duration::milliseconds(300),
        );

        $elapsed = $clock->timestamp()->getMilliseconds() - $ms;

        $this->assertSame(300, $elapsed);
    }
}
