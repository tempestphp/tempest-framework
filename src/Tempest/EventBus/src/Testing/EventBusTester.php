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
    private FakeEventBus $fakeEventBus;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function fake(): self
    {
        $this->fakeEventBus = new FakeEventBus($this->container->get(EventBusConfig::class));
        $this->container->singleton(EventBus::class, $this->fakeEventBus);

        return $this;
    }

    public function assertDispatched(string|object $event, ?Closure $callback = null, ?int $count = null): self
    {
        $this->assertFaked();

        Assert::assertNotNull(
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

    public function assertNotDispatched(string|object $event): self
    {
        $this->assertFaked();

        Assert::assertEmpty($this->findDispatches($event), 'The event was dispatched.');

        return $this;
    }

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
        $eventName = match (true) {
            $event instanceof BackedEnum => $event->value,
            $event instanceof UnitEnum => $event->name,
            is_string($event) => $event,
            default => $event::class,
        };

        return $this->fakeEventBus->eventBusConfig->handlers[$eventName] ?? [];
    }

    private function assertFaked(): self
    {
        Assert::assertTrue(isset($this->fakeEventBus), 'No fake event bus has been set.');

        return $this;
    }
}
