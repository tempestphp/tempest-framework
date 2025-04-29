<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Console\Fixtures\ScheduledCommand;

/**
 * @internal
 */
final class RunTaskCommandTest extends FrameworkIntegrationTestCase
{
    public function test_run_task(): void
    {
        $this->console
            ->call('schedule:task ' . ScheduledCommand::class . '::command')
            ->assertContains(ScheduledCommand::class . '::command')
            ->assertContains('Starting')
            ->assertContains('Done');
    }

    public function test_unknown_task(): void
    {
        $this->console
            ->call('schedule:task foo')
            ->assertContains('Invalid task');
    }

    public function test_invalid_task(): void
    {
        $this->console
            ->call('schedule:task ' . ScheduledCommand::class . '::unknown')
            ->assertContains(ScheduledCommand::class . '::unknown() does not exist');
    }
}
