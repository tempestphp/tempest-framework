<?php

declare(strict_types=1);

namespace Tempest\Clock\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tempest\Clock\GenericClock;
use Tempest\DateTime\Duration;

use const Tempest\DateTime\NANOSECONDS_PER_MILLISECOND;

/**
 * @internal
 */
final class GenericClockTest extends TestCase
{
    public function test_that_generic_clock_returns_the_current_date_time(): void
    {
        $dateTimeBefore = new DateTimeImmutable('now');
        $clockDateTime = new GenericClock()->now();
        $dateTimeAfter = new DateTimeImmutable('now');

        $this->assertGreaterThanOrEqual($dateTimeBefore->getTimestamp(), $clockDateTime->getTimestamp()->getSeconds());
        $this->assertLessThanOrEqual($dateTimeAfter->getTimestamp(), $clockDateTime->getTimestamp()->getSeconds());
    }

    public function test_that_generic_clock_returns_the_current_time(): void
    {
        $timeBefore = new DateTimeImmutable()->getTimestamp();
        $clockTime = new GenericClock()->timestamp();
        $timeAfter = new DateTimeImmutable()->getTimestamp();

        $this->assertGreaterThanOrEqual($timeBefore, $clockTime);
        $this->assertLessThanOrEqual($timeAfter, $clockTime);
    }

    public function test_that_generic_clock_sleeps(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Sleeping on Windows is not precise enough.');
        }

        $timeBefore = hrtime(true);
        new GenericClock()->sleep(milliseconds: 250);
        $timeAfter = hrtime(true);

        $this->assertGreaterThanOrEqual($timeBefore + (250 * NANOSECONDS_PER_MILLISECOND), $timeAfter);
    }

    public function test_that_generic_clock_sleeps_with_duration(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Sleeping on Windows is not precise enough.');
        }

        $timeBefore = hrtime(true);
        new GenericClock()->sleep(Duration::milliseconds(250));
        $timeAfter = hrtime(true);

        $this->assertGreaterThanOrEqual($timeBefore + (250 * NANOSECONDS_PER_MILLISECOND), $timeAfter);
    }
}
