<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\EventBus\EventBus;

    function event(string|object $event): void
    {
        $eventBus = get(EventBus::class);

        $eventBus->dispatch($event);
    }
}
