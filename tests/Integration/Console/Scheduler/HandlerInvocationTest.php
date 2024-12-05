<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Scheduler;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\ScheduledInvocation;
use Tempest\Reflection\MethodReflector;

/**
 * @internal
 */
final class HandlerInvocationTest extends TestCase
{
    public function test_name_gets_constructed_properly(): void
    {
        $invocation = new ScheduledInvocation(
            schedule: new Schedule(Every::DAY),
            handler: new MethodReflector(new ReflectionMethod(
                objectOrMethod: $this,
                method: 'handler',
            )),
        );

        $this->assertSame('schedule:task Tests\\\\Tempest\\\\Integration\\\\Console\\\\Scheduler\\\\HandlerInvocationTest::handler', $invocation->getCommandName());
    }

    // dummy handler method
    public function handler(): void
    {
    }
}
