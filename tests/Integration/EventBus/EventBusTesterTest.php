<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\EventBus;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\Testing\FakeEventBus;
use Tests\Tempest\Integration\EventBus\Fixtures\FakeEvent;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class EventBusTesterTest extends FrameworkIntegrationTestCase
{
    private EventBus $bus {
        get => $this->container->get(EventBus::class);
    }

    #[Test]
    public function fake(): void
    {
        $this->eventBus->preventEventHandling();

        $this->assertInstanceOf(FakeEventBus::class, $this->bus);
    }

    #[Test]
    public function assertion_on_real_event_bus(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageMatches('*Asserting against the event bus require*');

        $this->eventBus->assertDispatched('event-bus-fake-event');
    }

    #[Test]
    public function assert_dispatched(): void
    {
        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event');

        $this->bus->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class);
    }

    #[Test]
    public function assert_dispatched_with_callback(): void
    {
        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', function (string $event) {
            return $event === 'event-bus-fake-event';
        });

        $this->bus->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, function (FakeEvent $event) {
            return $event->value === 'foo';
        });
    }

    #[Test]
    public function assert_dispatched_with_count(): void
    {
        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', count: 1);

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', count: 2);

        $this->bus->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, count: 1);

        $this->bus->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, count: 2);

        $this->bus->dispatch(new FakeEvent('baz'));
        $this->eventBus->assertDispatched(FakeEvent::class, count: 3);
    }

    #[Test]
    public function assert_dispatched_with_count_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The number of dispatches does not match');

        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', count: 2);
    }

    #[Test]
    public function assert_dispatched_with_callback_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The callback failed');

        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertDispatched('event-bus-fake-event', function (string $event) {
            return $event !== 'event-bus-fake-event';
        });
    }

    #[Test]
    public function assert_dispatched_object_with_callback_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The callback failed');

        $this->eventBus->preventEventHandling();

        $this->bus->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertDispatched(FakeEvent::class, function (FakeEvent $event) {
            return $event->value === 'foobar';
        });
    }

    #[Test]
    public function assert_dispatched_failure(): void
    {
        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The event was not dispatched.');

        $this->eventBus->assertDispatched('this-was-not-dispatched');
    }

    #[Test]
    public function assert_not_dispatched(): void
    {
        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertNotDispatched('this-was-not-dispatched');
    }

    #[Test]
    public function assert_not_dispatched_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The event was dispatched');

        $this->eventBus->preventEventHandling();

        $this->bus->dispatch('event-bus-fake-event');
        $this->eventBus->assertNotDispatched('event-bus-fake-event');
    }

    #[Test]
    public function assert_not_dispatched_object_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The event was dispatched');

        $this->eventBus->preventEventHandling();

        $this->bus->dispatch(new FakeEvent('foo'));
        $this->eventBus->assertNotDispatched(FakeEvent::class);
    }

    #[Test]
    public function assert_listening_to(): void
    {
        $this->eventBus->preventEventHandling();

        $this->bus->listen(function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class);
        $this->eventBus->assertListeningTo(FakeEvent::class);
    }

    #[Test]
    public function assert_listening_to_count(): void
    {
        $this->eventBus->preventEventHandling();

        $this->bus->listen(function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class, count: 1);

        $this->bus->listen(function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class, count: 2);
    }

    #[Test]
    public function assert_listening_to_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The event is not being listened to');

        $this->eventBus->preventEventHandling();

        $this->eventBus->assertListeningTo(FakeEvent::class);
    }

    #[Test]
    public function assert_listening_to_count_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('The number of handlers does not match');

        $this->eventBus->preventEventHandling();

        $this->bus->listen(function (FakeEvent $_): never {
            throw new LogicException('This should not be called');
        });

        $this->eventBus->assertListeningTo(FakeEvent::class, count: 2);
    }

    public function allows_handling(): void
    {
        $this->eventBus->recordEventDispatches();

        $handled = false;
        $this->bus->listen(function (FakeEvent $_) use (&$handled): void {
            $handled = true;
        });

        $this->bus->dispatch(new FakeEvent('foo'));

        $this->eventBus->assertDispatched(FakeEvent::class);
        $this->eventBus->assertListeningTo(FakeEvent::class);

        $this->assertTrue($handled);
    }
}
