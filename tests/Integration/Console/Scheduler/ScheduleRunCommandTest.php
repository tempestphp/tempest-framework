<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Scheduler;

use Tempest\Console\Scheduler\GenericScheduler;
use Tempest\Drift\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ScheduleRunCommandTest extends FrameworkIntegrationTestCase
{
    public function test_invoke(): void
    {
        @unlink(GenericScheduler::getCachePath());

        $this->console
            ->call('schedule:run')
            ->assertSee('scheduled')
            ->assertSee('schedule:task Tests\\\\Tempest\\\\Integration\\\\Console\\\\Fixtures\\\\ScheduledCommand::method')
            ->assertSee('session:clean');

        $this->console
            ->call('schedule:run')
            ->assertNotSee('scheduled')
            ->assertNotSee('schedule:task Tests\\\\Tempest\\\\Integration\\\\Console\\\\Fixtures\\\\ScheduledCommand::method')
            ->assertNotSee('session:clean');
    }
}
