<?php

namespace Tests\Tempest\Integration\Cryptography;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Cryptography\Timelock;
use Tempest\DateTime\Duration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class TimelockTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function uses_mocked_clock(): void
    {
        $clock = $this->clock();
        $timelock = $this->container->get(Timelock::class);

        $ms = $clock->timestamp()->getMilliseconds();
        $timelock->invoke(
            callback: fn () => null,
            duration: Duration::milliseconds(10_000),
        );
        $elapsed = $clock->timestamp()->getMilliseconds() - $ms;

        $this->assertSame(10_000, $elapsed);
    }
}
