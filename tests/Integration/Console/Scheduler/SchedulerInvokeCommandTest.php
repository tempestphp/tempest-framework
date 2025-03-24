<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Scheduler;

use Tempest\Console\Commands\ScheduleTaskCommand;
use Tests\Tempest\Integration\Console\Fixtures\ScheduledCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SchedulerInvokeCommandTest extends FrameworkIntegrationTestCase
{
    public function test_scheduler_invoke_command_executes_handler(): void
    {
        $this->console
            ->call(ScheduleTaskCommand::NAME . ' ' . ScheduledCommand::class . '::method')
            ->assertContains('method got scheduled');
    }
}
