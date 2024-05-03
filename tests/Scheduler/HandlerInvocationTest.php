<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\Scheduler\HandlerInvocation;

/**
 * @internal
 * @small
 */
final class HandlerInvocationTest extends TestCase
{
    public function test_name_gets_constructed_properly(): void
    {
        $invocation = new HandlerInvocation(
            new ReflectionMethod(
                objectOrMethod: $this,
                method: 'handler',
            ),
        );

        $this->assertSame('Tests\\\Tempest\\\Console\\\Scheduler\\\HandlerInvocationTest::handler', $invocation->getName());
    }

    // dummy handler method
    public function handler()
    {

    }
}
