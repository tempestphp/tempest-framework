<?php

namespace Tempest\EventBus\Testing;

use BackedEnum;
use Closure;
use PHPUnit\Framework\Assert;
use Tempest\Container\Container;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;
use UnitEnum;

final class EventBusTester
{
    private ?FakeEventBus $fakeEventBus = null;
    private ?TrackedEventBus $trackedEventBus = null;

    public function __construct(
        private readonly Container $container,
    ) {
        $this->installTrackedBus();
    }

    /**
     * Prevents the registered event handlers from being called.
     */
    public function preventEventHandling(): self
    {
        $this->fakeEventBus = new FakeEventBus($this->container->get(EventBusConfig::class));
        $this->container->singleton(EventBus::class, $this->fakeEventBus);

        return $this;
    }

    private function installTrackedBus(): void
    {
        $current = $this->container->get(EventBus::class);

        if ($current instanceof FakeEventBus) {
            $this->fakeEventBus = $current;

            return;
        }

        if ($current instanceof TrackedEventBus) {
            $this->trackedEventBus = $current;

            return;
        }

        $this->trackedEventBus = new TrackedEventBus(
            inner: $current,
            eventBusConfig: $this->container->get(EventBusConfig::class),
        );

        $this->container->singleton(EventBus::class, $this->trackedEventBus);
    }

    private function getInspectionBus(): FakeEventBus|TrackedEventBus
    {
        if ($this->fakeEventBus !== null) {
            return $this->fakeEventBus;
        }

        if ($this->trackedEventBus === null) {
            $this->installTrackedBus();
        }

        return $this->trackedEventBus;
    }

    /**
     * Asserts that the given `$event` has been dispatched.
     *
     * @param null|Closure $callback A callback accepting the event instance. The assertion fails if the callback returns `false`.
     * @param null|int $count If specified, the assertion fails if the event has been dispatched a different amount of times.
     */
    public function assertDispatched(string|object $event, ?Closure $callback = null, ?int $count = null): self
    {
        $dispatches = $this->findDispatches($event);

        Assert::assertNotEmpty($dispatches, 'The event was not dispatched.');

        if ($count !== null) {
            Assert::assertCount($count, $dispatches, 'The number of dispatches does not match.');
        }

        if ($callback !== null) {
            foreach ($dispatches as $dispatch) {
                Assert::assertNotFalse($callback($dispatch), 'The callback failed.');
            }
        }

        return $this;
    }

    /**
     * Asserts that the specified `$event` has not been dispatched.
     */
    public function assertNotDispatched(string|object $event): self
    {
        Assert::assertEmpty($this->findDispatches($event), 'The event was dispatched.');

        return $this;
    }

    /**
     * Asserts that the specified `$event` is being listened to.
     *
     * @param null|int $count If specified, the assertion fails if there are a different amount of listeners.
     */
    public function assertListeningTo(string $event, ?int $count = null): self
    {
        Assert::assertNotEmpty(
            actual: $handlers = $this->findHandlersFor($event),
            message: 'The event is not being listened to.',
        );

        if ($count !== null) {
            Assert::assertSame($count, count($handlers), 'The number of handlers does not match.');
        }

        return $this;
    }

    private function findDispatches(string|object $event): array
    {
        // Collect dispatched events from both buses
        // This handles the case where components hold references to the old EventBus and continue dispatching to it
        $allDispatched = [];

        if ($this->trackedEventBus !== null) {
            $allDispatched = array_merge($allDispatched, $this->trackedEventBus->dispatched);
        }

        if ($this->fakeEventBus !== null) {
            $allDispatched = array_merge($allDispatched, $this->fakeEventBus->dispatched);
        }

        return array_filter($allDispatched, function (string|object $dispatched) use ($event) {
            if ($dispatched === $event) {
                return true;
            }

            if (class_exists($event) && $dispatched instanceof $event) {
                return true;
            }

            return false;
        });
    }

    /** @return array<\Tempest\EventBus\CallableEventHandler> */
    private function findHandlersFor(string|object $event): array
    {
        $eventName = match (true) {
            $event instanceof BackedEnum => $event->value,
            $event instanceof UnitEnum => $event->name,
            is_string($event) => $event,
            default => $event::class,
        };

        $inspectionBus = $this->getInspectionBus();

        return $inspectionBus->eventBusConfig->handlers[$eventName] ?? [];
    }
}
