<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Tempest\Support\Reflection\MethodReflector;

final class EventBusConfig
{
    public function __construct(
        /** @var \Tempest\EventBus\EventHandler[][] */
        public array $handlers = [],

        /** @var \Tempest\EventBus\EventBusMiddleware[] */
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

    public function addMiddleware(EventBusMiddleware $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }
}
