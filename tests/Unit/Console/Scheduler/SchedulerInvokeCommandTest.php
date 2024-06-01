<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Scheduler;

use Tempest\Console\Commands\ScheduleTaskCommand;
use Tests\Tempest\Unit\Console\ConsoleIntegrationTestCase;
use Tests\Tempest\Unit\Console\Fixtures\ScheduledCommand;

/**
 * @internal
 * @small
 */
final class SchedulerInvokeCommandTest extends ConsoleIntegrationTestCase
{
    public function test_scheduler_invoke_command_executes_handler()
    {
        $this->console
            ->call(ScheduleTaskCommand::NAME . ' ' . (ScheduledCommand::class . '::method'))
            ->assertContains('method got scheduled');
    }
}
