<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;
use Tempest\Reflection\MethodReflector;

final class EventBusConfig
{
    public function __construct(
        /** @var array<string,array<\Tempest\EventBus\CallableEventHandler>> */
        public array $handlers = [],

        /** @var array<array-key, class-string<\Tempest\EventBus\EventBusMiddleware>> */
        public array $middleware = [],
    ) {
    }

    public function addClosureHandler(string $event, Closure $handler): self
    {
        $handlerKey = spl_object_hash($handler);

        $this->handlers[$event][$handlerKey] = new CallableEventHandler(
            event: $event,
            handler: $handler,
        );

        return $this;
    }

    public function addClassMethodHandler(string $event, EventHandler $handler, MethodReflector $reflectionMethod): self
    {
        $handlerKey = $reflectionMethod->getDeclaringClass()->getName() . '::' . $reflectionMethod->getName();
        $handler->setEventName($event)->setHandler($reflectionMethod);

        $this->handlers[$event][$handlerKey] = new CallableEventHandler(
            event: $event,
            handler: $handler,
        );

        return $this;
    }

    /** @param class-string<\Tempest\EventBus\EventBusMiddleware> $middlewareClass */
    public function addMiddleware(string $middlewareClass): self
    {
        $this->middleware[] = $middlewareClass;

        return $this;
    }
}
