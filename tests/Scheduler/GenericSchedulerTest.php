<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

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
        $now = (new \DateTime())->setTime(0, 1);

        $output = new TestConsoleOutput();
        $handler = new ReflectionMethod(new PeriodicCommand($output), 'dummy');
        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0]->newInstance();

        $consoleCommand->setHandler($handler);

        $secondCommand = new ReflectionMethod(new PeriodicCommand($output), 'everyFive');
        $consoleCommand2 = $secondCommand->getAttributes(ConsoleCommand::class)[0]->newInstance();
        $consoleCommand2->setHandler($secondCommand);

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

    public function test_it_schedules_commands()
    {
        $now = (new \DateTime())->setTime(0, 5);

        $output = new TestConsoleOutput();

        $secondCommand = new ReflectionMethod(new PeriodicCommand($output), 'everyFive');
        $consoleCommand2 = $secondCommand->getAttributes(ConsoleCommand::class)[0]->newInstance();
        $consoleCommand2->setHandler($secondCommand);

        $scheduler = new GenericScheduler(
            new ConsoleConfig(scheduledCommands: [$consoleCommand2]),
            $output,
        );

        $scheduler->run($now);

        $this->assertStringContainsString(
            'Running command: ' . $consoleCommand2->getName(),
            $output->getLinesWithoutFormatting()[0],
        );

        $this->assertStringContainsString(
            'Command finished: ' . $consoleCommand2->getName(),
            $output->getLinesWithoutFormatting()[1],
        );

        $this->assertCount(2, $output->getLinesWithoutFormatting());

        $now = (new \DateTime())->setTime(0, 6);

        $output = new TestConsoleOutput();

        $scheduler = new GenericScheduler(
            new ConsoleConfig(scheduledCommands: [$consoleCommand2]),
            $output,
        );

        $scheduler->run($now);

        $this->assertEmpty($output->getLinesWithoutFormatting());
    }
}
