<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Debug;

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
}
