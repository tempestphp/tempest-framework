<?php

declare(strict_types=1);

namespace Tempest\Events;

use Tempest\Interface\Container;
use Tempest\Interface\EventBus;

final readonly class GenericEventBus implements EventBus
{
    public function __construct(
        private Container $container,
        private EventBusConfig $eventBusConfig,
    ) {
    }

    public function dispatch(object $event): void
    {
        /** @var \Tempest\Events\EventHandler[] $eventHandlers */
        $eventHandlers = $this->eventBusConfig->handlers[$event::class] ?? [];

        foreach ($eventHandlers as $handler) {
            $handlerClass = $this->container->get($handler->handler->getDeclaringClass()->getName());

            $handler->handler->invoke($handlerClass, $event);
        }
    }
}
