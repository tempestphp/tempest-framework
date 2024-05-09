<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use Tempest\Console\Commands\ScheduleTaskCommand;
use Tests\Tempest\Console\Fixtures\ScheduledCommand;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
final class SchedulerInvokeCommandTest extends TestCase
{
    public function test_scheduler_invoke_command_executes_handler()
    {
        $this->console
            ->call(ScheduleTaskCommand::NAME . ' ' . (ScheduledCommand::class . '::method'))
            ->assertContains('method got scheduled');
    }
}
