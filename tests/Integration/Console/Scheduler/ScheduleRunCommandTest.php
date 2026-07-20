<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Scheduler;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Scheduler\GenericScheduler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ScheduleRunCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function invoke(): void
    {
        $this->process->mockProcessResult();

        @unlink(GenericScheduler::getCachePath());

        $this->console
            ->call('schedule:run')
            ->assertSee('scheduled')
            ->assertSee('schedule:task Tests\\\\Tempest\\\\Integration\\\\Console\\\\Fixtures\\\\ScheduledCommand::method');

        $this->console
            ->call('schedule:run')
            ->assertNotSee('scheduled')
            ->assertNotSee('schedule:task Tests\\\\Tempest\\\\Integration\\\\Console\\\\Fixtures\\\\ScheduledCommand::method');
    }
}
