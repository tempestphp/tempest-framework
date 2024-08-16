<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;
use Tempest\Container\Container;

final readonly class GenericEventBus implements EventBus
{
    public function __construct(
        private Container $container,
        private EventBusConfig $eventBusConfig,
    ) {
    }

    public function dispatch(object $event): void
    {
        /** @var \Tempest\EventBus\EventHandler[] $eventHandlers */
        $eventHandlers = $this->eventBusConfig->handlers[$event::class] ?? [];

        foreach ($eventHandlers as $eventHandler) {
            $callable = $this->getCallable($eventHandler);

            $callable($event);
        }
    }

    private function getCallable(EventHandler $eventHandler): Closure
    {
        $callable = function (object $event) use ($eventHandler): void {
            $eventHandler->handler->invokeArgs(
                $this->container->get($eventHandler->handler->getDeclaringClass()->getName()),
                [$event],
            );
        };

        $middlewareStack = $this->eventBusConfig->middleware;

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = fn (object $event) => $this->container->get($middlewareClass)($event, $callable);
        }

        return $callable;
    }
}
