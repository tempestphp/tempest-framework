<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;
use Tempest\Container\Container;
use Tempest\Support\Str;

final readonly class GenericEventBus implements EventBus
{
    public function __construct(
        private Container $container,
        private EventBusConfig $eventBusConfig,
    ) {}

    public function listen(Closure $handler, ?string $event = null): void
    {
        $this->eventBusConfig->addClosureHandler($handler, $event);
    }

    public function dispatch(string|object $event): void
    {
        $eventHandlers = $this->resolveHandlers($event);

        $dispatch = $this->getCallable($eventHandlers);

        $dispatch($event);
    }

    /** @return \Tempest\EventBus\CallableEventHandler[] */
    private function resolveHandlers(string|object $event): array
    {
        $eventName = Str\parse($event) ?: $event::class;
        $handlers = $this->eventBusConfig->handlers[$eventName] ?? [];

        if (is_object($event)) {
            $interfaces = class_implements($event);

            foreach ($interfaces as $interface) {
                $handlers = [
                    ...$handlers,
                    ...($this->eventBusConfig->handlers[$interface] ?? []),
                ];
            }
        }

        return $handlers;
    }

    private function getCallable(array $eventHandlers): EventBusMiddlewareCallable
    {
        $callable = new EventBusMiddlewareCallable(function (string|object $event) use ($eventHandlers): void {
            foreach ($eventHandlers as $eventHandler) {
                $callable = $eventHandler->normalizeCallable($this->container);

                $callable($event);
            }
        });

        $middleware = $this->eventBusConfig->middleware;

        foreach ($middleware->unwrap() as $middlewareClass) {
            $callable = new EventBusMiddlewareCallable(fn (string|object $event) => $this->container->invoke(
                $middlewareClass,
                event: $event,
                next: $callable,
            ));
        }

        return $callable;
    }
}
