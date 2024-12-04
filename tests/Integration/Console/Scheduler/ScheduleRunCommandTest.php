<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Scheduler;

use Tempest\Console\Scheduler\GenericScheduler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ScheduleRunCommandTest extends FrameworkIntegrationTestCase
{
    public function test_invoke(): void
    {
        @unlink(GenericScheduler::CACHE_PATH);

        $this->console
            ->call('schedule:run')
            ->assertSee('scheduled completed')
            ->assertSee('schedule:task Tests\\\\Tempest\\\\Integration\\\\Console\\\\Fixtures\\\\ScheduledCommand::method completed')
            ->assertSee('session:clean completed')
            ->assertSee('Done');

        $this->console
            ->call('schedule:run')
            ->assertNotSee('scheduled completed')
            ->assertNotSee('schedule:task Tests\\\\Tempest\\\\Integration\\\\Console\\\\Fixtures\\\\ScheduledCommand::method completed')
            ->assertNotSee('session:clean completed')
            ->assertSee('Done');
    }
}
