<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Clock;

use DateInterval;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Tempest\Clock\GenericTimebox;
use Tempest\Clock\MockClock;

/**
 * @internal
 * @small
 */
final class GenericTimeboxTest extends TestCase
{
    public function test_it_runs_callbacks(): void
    {
        $timebox = new GenericTimebox(new MockClock());

        $timebox->run(fn () => $this->assertTrue(true), 0);
    }

    public function test_it_waits_for_the_given_time(): void
    {
        $now = new DateTime();

        $clock = new MockClock($now);

        $timebox = new GenericTimebox($clock);

        $timebox->run(fn () => $this->assertTrue(true), 10);

        $this->assertSame(
            $this->dateTimeToMicroseconds($now->add(DateInterval::createFromDateString('10 microseconds'))),
            $clock->utime(),
        );
    }

    public function test_it_waits_even_when_exception_is_thrown_and_throws_that_exception(): void
    {
        $now = new DateTime();

        $clock = new MockClock($now);

        $timebox = new GenericTimebox($clock);

        try {
            $timebox->run(fn () => throw new Exception("abc"), 100);
        } catch (Exception $exception) {
            $this->assertSame("abc", $exception->getMessage());
        }

        $this->assertSame(
            $this->dateTimeToMicroseconds($now->add(DateInterval::createFromDateString('100 microseconds'))),
            $clock->utime(),
        );
    }

    private function dateTimeToMicroseconds(DateTime $dateTime): int
    {
        return (int) $dateTime->format('u') + $dateTime->getTimestamp() * 1_000_000;
    }
}
