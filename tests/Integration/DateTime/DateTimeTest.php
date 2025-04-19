<?php

namespace Tests\Tempest\Integration\DateTime;

use Tempest\Clock\Clock;
use Tempest\DateTime\DateTime;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Clock\now;

final class DateTimeTest extends FrameworkIntegrationTestCase
{
    public function test_now_resolved_from_clock(): void
    {
        $clock = $this->clock('2024-01-01');

        $this->assertTrue(now()->equals('2024-01-01'));
        $this->assertTrue(DateTime::now()->equals('2024-01-01'));
        $this->assertTrue(
            $this->container
                ->get(Clock::class)
                ->now()
                ->equals('2024-01-01'),
        );

        $clock->setNow('2025-01-01 00:00:00');

        $this->assertTrue(now()->equals('2025-01-01 00:00:00'));

        $clock->sleep(milliseconds: 250);

        $this->assertEquals('1735689600250', now()->getTimestamp()->getMilliseconds());
    }
}
