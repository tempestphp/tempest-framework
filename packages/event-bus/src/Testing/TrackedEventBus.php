<?php

declare(strict_types=1);

namespace Tempest\EventBus\Testing;

use Closure;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;

final class TrackedEventBus implements EventBus
{
    public array $dispatched = [];

    public function __construct(
        public EventBus $inner,
        public EventBusConfig $eventBusConfig,
    ) {}

    public function listen(Closure $handler, ?string $event = null): void
    {
        $this->inner->listen($handler, $event);
    }

    public function dispatch(string|object $event): void
    {
        $this->dispatched[] = $event;

        $this->inner->dispatch($event);
    }
}
