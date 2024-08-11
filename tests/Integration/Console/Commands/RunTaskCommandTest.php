<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class RunTaskCommandTest extends FrameworkIntegrationTestCase
{
    public function test_run_task(): void
    {
        $this
            ->console
            ->call('schedule:task ' . \Tests\Tempest\Integration\Console\Fixtures\ScheduledCommand::class . '::command')
            ->assertContains(\Tests\Tempest\Integration\Console\Fixtures\ScheduledCommand::class . '::command')
            ->assertContains('Starting')
            ->assertContains('Done');
    }

    public function test_unknown_task(): void
    {
        $this
            ->console
            ->call('schedule:task foo')
            ->assertContains('Invalid task');
    }

    public function test_invalid_task(): void
    {
        $this
            ->console
            ->call('schedule:task ' . \Tests\Tempest\Integration\Console\Fixtures\ScheduledCommand::class . '::unknown')
            ->assertContains(\Tests\Tempest\Integration\Console\Fixtures\ScheduledCommand::class . '::unknown() does not exist');
    }
}
