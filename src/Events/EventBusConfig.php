<?php

declare(strict_types=1);

namespace Tempest\Events;

use ReflectionMethod;
use Tempest\Interface\CommandBusMiddleware;

final class EventBusConfig
{
    public function __construct(
        /** @var \Tempest\Events\EventHandler[][] */
        public array $handlers = [],

        /** @var \Tempest\Interface\CommandBusMiddleware[] */
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

    public function addMiddleware(CommandBusMiddleware $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }
}
