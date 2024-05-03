<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use App\Console\ScheduledCommand;
use Tempest\Console\Commands\SchedulerRunInvocationCommand;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
final class SchedulerInvokeCommandTest extends TestCase
{
    public function test_scheduler_invoke_command_executes_handler()
    {
        $this->console->call(SchedulerRunInvocationCommand::NAME . ' ' . (ScheduledCommand::class . '::method'))
            ->assertContains('method got scheduled');
    }
}
