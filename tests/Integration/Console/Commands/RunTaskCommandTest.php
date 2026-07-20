<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\Console\Fixtures\ScheduledCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RunTaskCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function run_task(): void
    {
        $this->console
            ->call('schedule:task ' . ScheduledCommand::class . '::command')
            ->assertContains(ScheduledCommand::class . '::command')
            ->assertContains('Starting')
            ->assertContains('Done');
    }

    #[Test]
    public function unknown_task(): void
    {
        $this->console
            ->call('schedule:task foo')
            ->assertContains('Invalid task');
    }

    #[Test]
    public function invalid_task(): void
    {
        $this->console
            ->call('schedule:task ' . ScheduledCommand::class . '::unknown')
            ->assertContains(ScheduledCommand::class . '::unknown() does not exist');
    }
}
