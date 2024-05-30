<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use DateTime;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\GenericScheduler;
use Tempest\Console\Scheduler\NullShellExecutor;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tests\Tempest\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class GenericSchedulerTest extends ConsoleIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // @todo: clean this up once file system is mockable
        if (file_exists(GenericScheduler::CACHE_PATH)) {
            unlink(GenericScheduler::CACHE_PATH);
        }
    }

    public function test_scheduler_executes_handlers()
    {
        $executor = $this->createMock(NullShellExecutor::class);

        $executor->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('(php tempest schedule:task Tests\\\Tempest\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &'));

        $config = new SchedulerConfig();
        $config->addMethodInvocation(
            new ReflectionMethod($this, 'handler'),
            new Schedule(Every::MINUTE)
        );

        $scheduler = new GenericScheduler($config, $executor);
        $scheduler->run();
    }

    public function test_scheduler_executes_commands()
    {
        $executor = $this->createMock(NullShellExecutor::class);

        $executor->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('(php tempest command) >> /dev/null &'));

        $config = new SchedulerConfig();
        $config->addCommandInvocation(
            new ReflectionMethod($this, 'command'),
            new ConsoleCommand('command'),
            new Schedule(Every::MINUTE)
        );

        $scheduler = new GenericScheduler($config, $executor);
        $scheduler->run();
    }

    public function test_scheduler_only_dispatches_the_command_in_desired_times()
    {
        $at = new DateTime('2024-05-01 00:00:00');

        $executor = $this->createMock(NullShellExecutor::class);

        $executor->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('(php tempest schedule:task Tests\\\Tempest\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &'));

        $config = new SchedulerConfig();
        $config->addMethodInvocation(
            new ReflectionMethod($this, 'handler'),
            new Schedule(Every::MINUTE)
        );

        $scheduler = new GenericScheduler($config, $executor);
        $scheduler->run($at);

        // command won't run twice in a row
        $scheduler->run($at);

        // nor when it's called before the next minute
        $scheduler->run($at->modify('+30 seconds'));

        $executor = $this->createMock(NullShellExecutor::class);

        $executor->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('(php tempest schedule:task Tests\\\Tempest\\\Console\\\Scheduler\\\GenericSchedulerTest::handler) >> /dev/null &'));

        $scheduler = new GenericScheduler($config, $executor);

        $scheduler->run($at->modify('+1 minute'));
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
