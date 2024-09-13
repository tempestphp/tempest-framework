<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Tempest\Reflection\MethodReflector;

final class EventBusConfig
{
    public function __construct(
        /** @var \Tempest\EventBus\EventHandler[][] */
        public array $handlers = [],

        /** @var array<array-key, class-string<\Tempest\EventBus\EventBusMiddleware>> */
        public array $middleware = [],
    ) {
    }

    public function addHandler(EventHandler $eventHandler, string $eventName, MethodReflector $reflectionMethod): self
    {
        $handlerKey = $reflectionMethod->getDeclaringClass()->getName() . '::' . $reflectionMethod->getName();

        $this->handlers[$eventName][$handlerKey] = $eventHandler
            ->setEventName($eventName)
            ->setHandler($reflectionMethod);

        return $this;
    }

    /** @param class-string<\Tempest\EventBus\EventBusMiddleware> $middlewareClass */
    public function addMiddleware(string $middlewareClass): self
    {
        $this->middleware[] = $middlewareClass;

        return $this;
    }
}
