<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Commands;

use Tests\Tempest\Unit\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
class RunTaskCommandTest extends ConsoleIntegrationTestCase
{
    public function test_run_task()
    {
        $this
            ->console
            ->call('schedule:task \Tests\Tempest\Unit\Console\Fixtures\ScheduledCommand::command')
            ->assertContains('\Tests\Tempest\Unit\Console\Fixtures\ScheduledCommand::command')
            ->assertContains('Starting')
            ->assertContains('Done');
    }

    public function test_unknown_task()
    {
        $this
            ->console
            ->call('schedule:task foo')
            ->assertContains('Invalid task');
    }

    public function test_invalid_task()
    {
        $this
            ->console
            ->call('schedule:task \Tests\Tempest\Unit\Console\Fixtures\ScheduledCommand::unknown')
            ->assertContains('Tests\Tempest\Unit\Console\Fixtures\ScheduledCommand::unknown() does not exist');
    }
}
