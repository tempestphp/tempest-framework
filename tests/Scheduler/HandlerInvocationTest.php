<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\Schedule;
use Tempest\Console\Scheduler\ScheduledInvocation;

/**
 * @internal
 * @small
 */
final class HandlerInvocationTest extends TestCase
{
    public function test_name_gets_constructed_properly(): void
    {
        $invocation = new ScheduledInvocation(
            schedule: new Schedule(Every::DAY),
            handler: new ReflectionMethod(
                objectOrMethod: $this,
                method: 'handler',
            )
        );

        $this->assertSame('schedule:task Tests\\\\Tempest\\\\Console\\\\Scheduler\\\\HandlerInvocationTest::handler', $invocation->getCommandName());
    }

    // dummy handler method
    public function handler()
    {
    }
}
