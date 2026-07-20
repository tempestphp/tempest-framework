<?php

namespace Tempest\Cryptography\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tempest\Clock\GenericClock;
use Tempest\Clock\MockClock;
use Tempest\Cryptography\Timelock;
use Tempest\DateTime\Duration;

final class TimelockTest extends TestCase
{
    use HasMoreIntegerAssertions;

    #[Test]
    public function callback_is_executed(): void
    {
        $clock = new GenericClock();
        $result = new Timelock($clock)->invoke(fn () => 'hello', Duration::zero());

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function locks_for_duration(): void
    {
        $clock = new GenericClock();
        $start = microtime(true);

        $timelock = new Timelock($clock);
        $timelock->invoke(fn () => null, Duration::milliseconds(100));

        $elapsed = microtime(true) - $start;

        $this->assertEqualsToMoreOrLess(0.1, $elapsed, margin: 0.015, windowsMargin: 0.025);
    }

    #[Test]
    public function return_early(): void
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

    #[Test]
    public function throws_exception_after_delay(): void
    {
        $clock = new GenericClock();
        $timelock = new Timelock($clock);

        $start = microtime(true);

        try {
            $timelock->invoke(
                callback: fn () => throw new RuntimeException('This is an error.'),
                duration: Duration::milliseconds(100),
            );
        } catch (RuntimeException) {
            $elapsed = microtime(true) - $start;
            $this->assertEqualsToMoreOrLess(0.1, $elapsed, margin: 0.015, windowsMargin: 0.025);
        }
    }

    #[Test]
    public function uses_clock_to_sleep(): void
    {
        $clock = new MockClock();
        $timelock = new Timelock($clock);

        $ms = $clock->timestamp()->getMilliseconds();

        $timelock->invoke(
            callback: fn () => null,
            duration: Duration::milliseconds(300),
        );

        $elapsed = $clock->timestamp()->getMilliseconds() - $ms;

        // Even if we mock the clock, there's a `microtime` call that may be off by a few ms
        $this->assertEqualsToMoreOrLess(300, $elapsed, margin: 2);
    }
}
