<?php

namespace Tempest\EventBus\Testing;

use Closure;
use Tempest\EventBus\CallableEventHandler;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\GenericEventBus;
use UnitEnum;

final class FakeEventBus implements EventBus
{
    /** @var array<string|object> */
    public array $dispatched = [];

    /** @var array<string,array<CallableEventHandler>> */
    public array $handlers {
        get => $this->genericEventBus->eventBusConfig->handlers;
    }

    public function __construct(
        private(set) GenericEventBus $genericEventBus,
        public bool $preventHandling = true,
    ) {}

    public function dispatch(string|object $event): void
    {
        $this->dispatched[] = $event;

        if ($this->preventHandling === false) {
            $this->genericEventBus->dispatch($event);
        }
    }

    public function listen(Closure $handler, string|UnitEnum|null $event = null): void
    {
        $this->genericEventBus->listen($handler, $event);
    }
}
