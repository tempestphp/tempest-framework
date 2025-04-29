<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Integration;

use LogicException;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\Testing\FakeEventBus;
use Tempest\EventBus\Tests\Integration\Fixtures\FakeEvent;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class EventBusTesterTest extends FrameworkIntegrationTestCase
{
    public function test_fake(): void
    {
        $this->eventBus->preventEventHandling();

        $this->assertInstanceOf(FakeEventBus::class, $this->container->get(EventBus::class));
    }

    public function test_assertion_on_real_event_bus(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Asserting against the event bus require the `preventEventHandling()` method to be called first.');

        $this->eventBus->assertDispatched('event-bus-fake-event');
    }

    public function test_assert_dispatched(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event');

        $this->container->get(EventBus::class)->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class);
    }

    public function test_assert_dispatched_with_callback(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', function (string $event) {
            return $event === 'event-bus-fake-event';
        });

        $this->container->get(EventBus::class)->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, function (FakeEvent $event) {
            return $event->value === 'foo';
        });
    }

    public function test_assert_dispatched_with_count(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', count: 1);

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', count: 2);

        $this->container->get(EventBus::class)->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, count: 1);

        $this->container->get(EventBus::class)->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, count: 2);

        $this->container->get(EventBus::class)->dispatch(new FakeEvent('baz'));
        $this->eventBus->assertDispatched(FakeEvent::class, count: 3);
    }

    public function test_assert_dispatched_with_count_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The number of dispatches does not match');

        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', count: 2);
    }

    public function test_assert_dispatched_with_callback_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The callback failed');

        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', function (string $event) {
            return $event !== 'event-bus-fake-event';
        });
    }

    public function test_assert_dispatched_object_with_callback_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The callback failed');

        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, function (FakeEvent $event) {
            return $event->value === 'foobar';
        });
    }

    public function test_assert_not_dispatched(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertNotDispatched('this-was-not-dispatched');
    }

    public function test_assert_not_dispatched_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The event was dispatched');

        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch('event-bus-fake-event');
        $this->eventBus->assertNotDispatched('event-bus-fake-event');
    }

    public function test_assert_not_dispatched_object_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The event was dispatched');

        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertNotDispatched(FakeEvent::class);
    }

    public function test_assert_listening_to(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->listen(FakeEvent::class, function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class);
        $this->eventBus->assertListeningTo(FakeEvent::class);
    }

    public function test_assert_listening_to_count(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->listen(FakeEvent::class, function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class, count: 1);

        $this->container->get(EventBus::class)->listen(FakeEvent::class, function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class, count: 2);
    }

    public function test_assert_listening_to_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The event is not being listened to');

        $this->eventBus->preventEventHandling();

        $this->eventBus->assertListeningTo(FakeEvent::class);
    }

    public function test_assert_listening_to_count_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The number of handlers does not match');

        $this->eventBus->preventEventHandling();

        $this->container->get(EventBus::class)->listen(FakeEvent::class, function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class, count: 2);
    }
}
