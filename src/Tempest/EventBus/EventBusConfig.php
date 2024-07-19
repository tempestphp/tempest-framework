<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use ReflectionMethod;

final class EventBusConfig
{
    public function __construct(
        /** @var \Tempest\EventBus\EventHandler[][] */
        public array $handlers = [],

        /** @var \Tempest\EventBus\EventBusMiddleware[] */
        public array $middleware = [],
    ) {
    }

    public function addHandler(EventHandler $eventHandler, string $eventName, ReflectionMethod $reflectionMethod): self
    {
        $this->handlers[$eventName][] = $eventHandler
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
