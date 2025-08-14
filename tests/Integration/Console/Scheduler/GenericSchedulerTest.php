<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Scheduler;

use DateTime;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\GenericScheduler;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Process\ProcessExecutor;
use Tempest\Reflection\MethodReflector;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericSchedulerTest extends FrameworkIntegrationTestCase
{
    private ProcessExecutor $executor {
        get => $this->container->get(ProcessExecutor::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // @todo: clean this up once file system is mockable
        if (file_exists(GenericScheduler::getCachePath())) {
            unlink(GenericScheduler::getCachePath());
        }
    }

    public function test_scheduler_executes_handlers(): void
    {
        $this->process->mockProcessResult();

        $config = new SchedulerConfig();

        $config->addMethodInvocation(
            new MethodReflector(new ReflectionMethod($this, 'handler')),
            new Schedule(Every::MINUTE),
        );

        $argumentBag = new ConsoleArgumentBag(['tempest']);
        $scheduler = new GenericScheduler($config, $argumentBag, $this->executor);

        $scheduler->run();

        $this->process->assertCommandRan(
            '(' . PHP_BINARY . ' tempest schedule:task Tests\\\Tempest\\\Integration\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &',
        );
    }

    public function test_scheduler_executes_commands(): void
    {
        $this->process->mockProcessResult();

        $config = new SchedulerConfig();

        $config->addCommandInvocation(
            new MethodReflector(new ReflectionMethod($this, 'command')),
            new ConsoleCommand('command'),
            new Schedule(Every::MINUTE),
        );

        $argumentBag = new ConsoleArgumentBag(['tempest']);
        $scheduler = new GenericScheduler($config, $argumentBag, $this->executor);
        $scheduler->run();

        $this->process->assertCommandRan(
            '(' . PHP_BINARY . ' tempest command) >> /dev/null &',
        );
    }

    public function test_scheduler_only_dispatches_the_command_in_desired_times(): void
    {
        $this->process->mockProcessResult();
        $at = new DateTime('2024-05-01 00:00:00');

        $config = new SchedulerConfig();
        $config->addMethodInvocation(
            new MethodReflector(new ReflectionMethod($this, 'handler')),
            new Schedule(Every::MINUTE),
        );

        $argumentBag = new ConsoleArgumentBag(['tempest']);
        $scheduler = new GenericScheduler($config, $argumentBag, $this->executor);
        $scheduler->run($at);

        $this->process->assertCommandRan(
            '(' . PHP_BINARY . ' tempest schedule:task Tests\\\Tempest\\\Integration\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &',
        );

        // command won't run twice in a row
        $scheduler->run($at);

        // nor when it's called before the next minute
        $scheduler->run($at->modify('+30 seconds'));

        $scheduler = new GenericScheduler($config, $argumentBag, $this->executor);

        $scheduler->run($at->modify('+1 minute'));

        $this->process->assertCommandRan(
            '(' . PHP_BINARY . ' tempest schedule:task Tests\\\Tempest\\\Integration\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &',
        );

        $this->process->assertRanTimes(
            command: '(' . PHP_BINARY . ' tempest schedule:task Tests\\\Tempest\\\Integration\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &',
            times: 2,
        );
    }

    // dummy handler for testing
    public function handler(): void
    {
    }

    // dummy command for testing
    public function command(): void
    {
    }
}
