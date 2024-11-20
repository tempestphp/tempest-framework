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
use Tempest\Console\Scheduler\NullShellExecutor;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Reflection\MethodReflector;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericSchedulerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // @todo: clean this up once file system is mockable
        if (file_exists(GenericScheduler::CACHE_PATH)) {
            unlink(GenericScheduler::CACHE_PATH);
        }
    }

    public function test_scheduler_executes_handlers(): void
    {
        $config = new SchedulerConfig();

        $config->addMethodInvocation(
            new MethodReflector(new ReflectionMethod($this, 'handler')),
            new Schedule(Every::MINUTE),
        );

        $executor = new NullShellExecutor();
        $argumentBag = new ConsoleArgumentBag(['tempest']);
        $scheduler = new GenericScheduler($config, $argumentBag, $executor);

        $scheduler->run();

        $this->assertSame(
            '(' . PHP_BINARY . ' tempest schedule:task Tests\\\Tempest\\\Integration\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &',
            $executor->executedCommand,
        );
    }

    public function test_scheduler_executes_commands(): void
    {
        $config = new SchedulerConfig();

        $config->addCommandInvocation(
            new MethodReflector(new ReflectionMethod($this, 'command')),
            new ConsoleCommand('command'),
            new Schedule(Every::MINUTE),
        );

        $executor = new NullShellExecutor();
        $argumentBag = new ConsoleArgumentBag(['tempest']);
        $scheduler = new GenericScheduler($config, $argumentBag, $executor);
        $scheduler->run();

        $this->assertSame(
            '(' . PHP_BINARY . ' tempest command) >> /dev/null &',
            $executor->executedCommand,
        );
    }

    public function test_scheduler_only_dispatches_the_command_in_desired_times(): void
    {
        $at = new DateTime('2024-05-01 00:00:00');

        $config = new SchedulerConfig();
        $config->addMethodInvocation(
            new MethodReflector(new ReflectionMethod($this, 'handler')),
            new Schedule(Every::MINUTE),
        );

        $executor = new NullShellExecutor();
        $argumentBag = new ConsoleArgumentBag(['tempest']);
        $scheduler = new GenericScheduler($config, $argumentBag, $executor);
        $scheduler->run($at);

        $this->assertSame(
            '(' . PHP_BINARY . ' tempest schedule:task Tests\\\Tempest\\\Integration\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &',
            $executor->executedCommand,
        );

        // command won't run twice in a row
        $scheduler->run($at);

        // nor when it's called before the next minute
        $scheduler->run($at->modify('+30 seconds'));

        $executor = new NullShellExecutor();

        $scheduler = new GenericScheduler($config, $argumentBag, $executor);

        $scheduler->run($at->modify('+1 minute'));

        $this->assertSame(
            '(' . PHP_BINARY . ' tempest schedule:task Tests\\\Tempest\\\Integration\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &',
            $executor->executedCommand,
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
