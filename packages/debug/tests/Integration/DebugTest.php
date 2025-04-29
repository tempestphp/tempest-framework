<?php

declare(strict_types=1);

namespace Tempest\Debug\Tests\Integration;

use stdClass;
use Tempest\Debug\Debug;
use Tempest\Debug\ItemsDebugged;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\EventBus\EventBus;

/**
 * @internal
 */
final class DebugTest extends FrameworkIntegrationTestCase
{
    public function test_event(): void
    {
        $class = new stdClass();

        $eventBus = $this->container->get(EventBus::class);
        $eventBus->listen(ItemsDebugged::class, function (ItemsDebugged $event) use ($class): void {
            $this->assertSame(['foo', $class], $event->items);
        });

        Debug::resolve()->log(['foo', $class], writeToLog: false, writeToOut: false);
    }
}
