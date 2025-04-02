<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;
use Tempest\Core\Middleware;
use Tempest\Reflection\MethodReflector;

final class EventBusConfig
{
    public function __construct(
        /** @var array<string,array<\Tempest\EventBus\CallableEventHandler>> */
        public array $handlers = [],

        /** @var Middleware<\Tempest\EventBus\EventBusMiddleware> */
        public Middleware $middleware = new Middleware(),
    ) {}

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
}
