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

    public function listen(string|object $event, Closure $handler): void
    {
        $this->eventBusConfig->addClosureHandler($event, $handler);
    }

    public function dispatch(string|object $event): void
    {
        $eventName = match(true) {
            $event instanceof BackedEnum => $event->value,
            $event instanceof UnitEnum => $event->name,
            is_string($event) => $event,
            default => $event::class,
        };

        /** @var \Tempest\EventBus\CallableEventHandler[] $eventHandlers */
        $eventHandlers = $this->eventBusConfig->handlers[$eventName] ?? [];

        foreach ($eventHandlers as $eventHandler) {
            $callable = $eventHandler->normalizeCallable($this->container);
            $callable = $this->applyMiddleware($callable);

            $callable($event);
        }
    }

    private function applyMiddleware(Closure $handler): Closure
    {
        $middlewareStack = $this->eventBusConfig->middleware;

        while ($middlewareClass = array_pop($middlewareStack)) {
            $handler = fn (string|object $event) => $this->container->invoke(
                $middlewareClass,
                event: $event,
                next: $handler
            );
        }

        return $handler;
    }
}
