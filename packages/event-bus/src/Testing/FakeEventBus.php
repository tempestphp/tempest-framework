<?php

namespace Tempest\EventBus\Testing;

use Closure;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;

final class FakeEventBus implements EventBus
{
    public array $dispatched = [];

    public function __construct(
        public EventBusConfig $eventBusConfig,
    ) {}

    public function listen(Closure $handler, ?string $event = null): void
    {
        $this->eventBusConfig->addClosureHandler($handler, $event);
    }

    public function dispatch(string|object $event): void
    {
        $this->dispatched[] = $event;
    }
}
