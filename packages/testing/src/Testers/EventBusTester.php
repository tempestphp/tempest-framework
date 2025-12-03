<?php

namespace Tempest\Testing\Testers;

use Closure;
use Tempest\EventBus\EventBus;
use Tempest\Support\Str;

use function Tempest\Testing\test;

final class EventBusTester implements EventBus
{
    /** @var object[][] */
    private array $dispatched = [];

    private bool $allowPropagation = true;

    public function __construct(
        private readonly EventBus $eventBus,
    ) {}

    public function dispatch(object|string $event): void
    {
        $eventName = Str\parse($event) ?: $event::class;

        $this->dispatched[$eventName][] = $event;

        if ($this->allowPropagation) {
            $this->eventBus->dispatch($event);
        }
    }

    public function listen(Closure $handler, ?string $event = null): void
    {
        $this->eventBus->listen($handler, $event);
    }

    public function allowPropagation(): self
    {
        $this->allowPropagation = true;

        return $this;
    }

    public function preventPropagation(): self
    {
        $this->allowPropagation = false;

        return $this;
    }

    public function wasDispatched(
        string $expectedEventClass,
        ?Closure $eventTester = null,
    ): self {
        test($this->dispatched)->hasKey($expectedEventClass);

        if ($eventTester) {
            foreach ($this->dispatched[$expectedEventClass] as $event) {
                $eventTester($event);
            }
        }

        return $this;
    }

    public function wasNotDispatched(
        string $expectedEventClass,
    ): self {
        test(fn () => $this->wasDispatched($expectedEventClass))->fails();

        return $this;
    }
}
