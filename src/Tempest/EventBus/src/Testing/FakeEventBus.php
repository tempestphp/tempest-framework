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

    public function listen(string|object $event, Closure $handler): void
    {
        $this->eventBusConfig->addClosureHandler($event, $handler);
    }

    public function dispatch(string|object $event): void
    {
        $this->dispatched[] = $event;
    }
}
