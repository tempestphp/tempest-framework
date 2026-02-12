<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Debug;

use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tempest\Debug\Debug;
use Tempest\Debug\ItemsDebugged;
use Tempest\EventBus\EventBus;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DebugTest extends FrameworkIntegrationTestCase
{
    public function test_event(): void
    {
        $class = new stdClass();

        $eventBus = $this->container->get(EventBus::class);
        $eventBus->listen(function (ItemsDebugged $event) use ($class): void {
            $this->assertSame(['foo', $class], $event->items);
        });

        Debug::resolve()->log(['foo', $class], writeToLog: false, writeToOut: false);
    }

    #[Test]
    public function recursive_debug_inside_event_listener_does_not_cause_infinite_loop(): void
    {
        $dispatchCount = 0;

        $eventBus = $this->container->get(EventBus::class);
        $eventBus->listen(function (ItemsDebugged $event) use (&$dispatchCount): void {
            $dispatchCount++;

            Debug::resolve()->log(['recursive call'], writeToLog: false, writeToOut: false);
        });

        Debug::resolve()->log(['initial call'], writeToLog: false, writeToOut: false);

        $this->assertSame(1, $dispatchCount);
    }
}
