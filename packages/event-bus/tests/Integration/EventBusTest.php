<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Integration;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tests\Tempest\Fixtures\Events\DiscoveredEventBusMiddleware;
use Tests\Tempest\Fixtures\Events\EnumEvent;
use Tests\Tempest\Fixtures\Events\EventInterfaceImplementation;
use Tests\Tempest\Fixtures\Events\TestEventHandler;
use Tests\Tempest\Fixtures\Handlers\EventInterfaceHandler;

use function Tempest\event;

/**
 * @internal
 */
final class EventBusTest extends FrameworkIntegrationTestCase
{
    public function test_string_events(): void
    {
        TestEventHandler::$fromStringEvent = false;

        event('string-event');

        $this->assertTrue(TestEventHandler::$fromStringEvent);
    }

    public function test_enum_events(): void
    {
        TestEventHandler::$fromEnumEvent = false;

        event(EnumEvent::Foo);

        $this->assertTrue(TestEventHandler::$fromEnumEvent);
    }

    public function test_interface_events_are_discovered(): void
    {
        EventInterfaceHandler::$itHappened = false;

        event(new EventInterfaceImplementation());

        $this->assertTrue(EventInterfaceHandler::$itHappened);
    }

    public function test_discovered_middleware(): void
    {
        DiscoveredEventBusMiddleware::$hit = false;

        event(EnumEvent::Foo);

        $this->assertTrue(DiscoveredEventBusMiddleware::$hit);
    }
}
