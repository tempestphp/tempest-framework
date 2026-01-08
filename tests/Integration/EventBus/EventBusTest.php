<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\EventBus;

use Tests\Tempest\Fixtures\Events\DiscoveredEventBusMiddleware;
use Tests\Tempest\Fixtures\Events\EnumEvent;
use Tests\Tempest\Fixtures\Events\EventForListenerWithoutPropagation;
use Tests\Tempest\Fixtures\Events\EventInterfaceImplementation;
use Tests\Tempest\Fixtures\Events\EventWithoutPropagation;
use Tests\Tempest\Fixtures\Events\HandlersForEventWithListenerWithoutPropagation;
use Tests\Tempest\Fixtures\Events\HandlersForEventWithoutPropagation;
use Tests\Tempest\Fixtures\Events\OtherEnumEvent;
use Tests\Tempest\Fixtures\Events\TestEventHandler;
use Tests\Tempest\Fixtures\Handlers\EventInterfaceHandler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\EventBus\event;

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

    public function test_enum_events_with_colliding_names(): void
    {
        TestEventHandler::$fromEnumEvent = false;

        event(OtherEnumEvent::Foo);

        $this->assertFalse(TestEventHandler::$fromEnumEvent);
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

    public function test_event_without_propagation(): void
    {
        $handler = $this->get(HandlersForEventWithoutPropagation::class);
        $handler->count = 0;

        event(new EventWithoutPropagation());

        $this->assertSame(1, $handler->count);
    }

    public function test_listener_without_propagation(): void
    {
        $handler = $this->get(HandlersForEventWithListenerWithoutPropagation::class);
        $handler->count = 0;

        event(new EventForListenerWithoutPropagation());

        $this->assertSame(1, $handler->count);
    }
}
