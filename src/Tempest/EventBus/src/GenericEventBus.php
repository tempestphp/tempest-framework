<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use BackedEnum;
use Closure;
use Tempest\Container\Container;
use UnitEnum;

final readonly class GenericEventBus implements EventBus
{
    public function __construct(
        private Container $container,
        private EventBusConfig $eventBusConfig,
    ) {
    }

    public function dispatch(string|object $event): void
    {
        $eventName = match(true) {
            $event instanceof BackedEnum => $event->value,
            $event instanceof UnitEnum => $event->name,
            is_string($event) => $event,
            default => $event::class,
        };

        /** @var \Tempest\EventBus\EventHandler[] $eventHandlers */
        $eventHandlers = $this->eventBusConfig->handlers[$eventName] ?? [];

        foreach ($eventHandlers as $eventHandler) {
            $callable = $this->getCallable($eventHandler);

            $callable($event);
        }
    }

    private function getCallable(EventHandler $eventHandler): Closure
    {
        $callable = function (string|object $event) use ($eventHandler): void {
            $eventHandler->handler->invokeArgs(
                $this->container->get($eventHandler->handler->getDeclaringClass()->getName()),
                [$event],
            );
        };

        $middlewareStack = $this->eventBusConfig->middleware;

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = fn (string|object $event) => $this->container->get($middlewareClass)($event, $callable);
        }

        return $callable;
    }
}
