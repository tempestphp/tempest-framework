<?php

namespace Tempest\EventBus\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use Tempest\Container\Container;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;
use Tempest\Support\Str;

final class EventBusTester
{
    private FakeEventBus $fakeEventBus;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * Prevents the registered event handlers from being called.
     */
    public function preventEventHandling(): self
    {
        $this->fakeEventBus = new FakeEventBus($this->container->get(EventBusConfig::class));
        $this->container->singleton(EventBus::class, $this->fakeEventBus);

        return $this;
    }

    /**
     * Asserts that the given `$event` has been dispatched.
     *
     * @param null|Closure $callback A callback accepting the event instance. The assertion fails if the callback returns `false`.
     * @param null|int $count If specified, the assertion fails if the event has been dispatched a different amount of times.
     */
    public function assertDispatched(string|object $event, ?Closure $callback = null, ?int $count = null): self
    {
        $this->assertFaked();

        Assert::assertNotEmpty(
            actual: $dispatches = $this->findDispatches($event),
            message: 'The event was not dispatched.',
        );

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
        $this->assertFaked();

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
        $this->assertFaked();

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
        return array_filter($this->fakeEventBus->dispatched, function (string|object $dispatched) use ($event) {
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
        $eventName = Str\parse($event) ?: $event::class;

        return $this->fakeEventBus->eventBusConfig->handlers[$eventName] ?? [];
    }

    private function assertFaked(): self
    {
        Assert::assertTrue(isset($this->fakeEventBus), 'Asserting against the event bus require the `preventEventHandling()` method to be called first.');

        return $this;
    }
}
