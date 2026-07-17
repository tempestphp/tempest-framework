<?php

namespace Tempest\EventBus;

use Tempest\Container\Resettable;

final readonly class EventBusReset implements Resettable
{
    public function __construct(
        private EventBusConfig $eventBusConfig,
    ) {}

    public function reset(): void
    {
        $this->eventBusConfig->closureHandlers = [];
    }
}
