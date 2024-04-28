<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use DateTime;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\Scheduler\GenericScheduler;
use Tempest\Console\Testing\TestConsoleOutput;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
final class GenericSchedulerTest extends TestCase
{
    public function test_it_works_with_every_minute_scheduling()
    {
        $now = (new DateTime())->setTime(0, 1);

        $scheduler = new GenericScheduler(
            new ConsoleConfig(scheduledCommands: [$consoleCommand, $consoleCommand2]),
            $output,
        );

        $scheduler->run($now);

        $this->assertStringContainsString(
            'Running command: ' . $consoleCommand->getName(),
            $output->getLinesWithoutFormatting()[0],
        );

        $this->assertStringContainsString(
            'Command finished: ' . $consoleCommand->getName(),
            $output->getLinesWithoutFormatting()[1],
        );

        $this->assertCount(2, $output->getLinesWithoutFormatting());
    }
}
