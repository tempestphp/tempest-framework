<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use ReflectionMethod;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleCommand;
use Tests\Tempest\Console\TestCase;
use Tempest\Console\Scheduler\Scheduler;
use Tempest\Console\Testing\TestConsoleOutput;
use Tempest\Console\Scheduler\GenericScheduler;

/**
 * @internal
 * @small
 */
final class GenericSchedulerTest extends TestCase
{
    public function test_it_schedules_commands()
    {
        $output = new TestConsoleOutput();

        $handler = new ReflectionMethod(new PeriodicCommand($output), 'dummy');

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0]->newInstance();

        $consoleCommand->setHandler($handler);

        $scheduler = new GenericScheduler(
            new ConsoleConfig(scheduledCommands: [$consoleCommand]),
            $output,
        );

        $scheduler->run();

        $this->assertStringContainsString(
            'Running command: ' . $consoleCommand->getName(),
            $output->getLinesWithoutFormatting()[0],
        );

        $this->assertStringContainsString(
            'Command finished: ' . $consoleCommand->getName(),
            $output->getLinesWithoutFormatting()[1],
        );
    }
}
