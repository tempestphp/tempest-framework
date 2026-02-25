<?php

namespace Tempest\EventBus\Testing;

use Closure;
use Tempest\EventBus\CallableEventHandler;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;
use UnitEnum;

final class FakeEventBus implements EventBus
{
    /** @var array<string|object> */
    public array $dispatched = [];

    /** @var array<string,array<CallableEventHandler>> */
    public array $handlers {
        get => $this->eventBusConfig->handlers;
    }

    public function __construct(
        private(set) EventBus $eventBus,
        private(set) EventBusConfig $eventBusConfig,
        public bool $preventHandling = true,
    ) {}

    public function dispatch(string|object $event): void
    {
        $this->dispatched[] = $event;

        if ($this->preventHandling === false) {
            $this->eventBus->dispatch($event);
        }
    }

    public function listen(Closure $handler, string|UnitEnum|null $event = null): void
    {
        $this->eventBus->listen($handler, $event);
    }
}
