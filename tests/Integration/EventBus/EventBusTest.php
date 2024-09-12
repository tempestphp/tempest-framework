<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\EventBus;

use function Tempest\event;
use Tests\Tempest\Fixtures\Events\EnumEvent;
use Tests\Tempest\Fixtures\Events\TestEventHandler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
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
}