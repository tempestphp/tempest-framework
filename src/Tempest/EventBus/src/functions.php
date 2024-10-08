<?php

declare(strict_types=1);

namespace Tempest {

    use Closure;
    use Tempest\EventBus\EventBus;
    use Tempest\EventBus\EventBusConfig;

    function event(string|object $event): void
    {
        $eventBus = get(EventBus::class);

        $eventBus->dispatch($event);
    }

    function listen(string|object $event, Closure $handler): void
    {
        $config = get(EventBusConfig::class);

        $config->addClosureHandler($event, $handler);
    }
}
