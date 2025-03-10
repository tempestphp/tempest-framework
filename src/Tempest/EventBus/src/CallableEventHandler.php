<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;
use Tempest\Container\Container;

final class CallableEventHandler
{
    public function __construct(
        public null|string|object $event,
        public EventHandler|Closure $handler,
    ) {
    }

    public function normalizeCallable(Container $container): Closure
    {
        if ($this->handler instanceof Closure) {
            return function (string|object $event) use ($container): void {
                $container->invoke($this->handler, event: $event);
            };
        }

        return function (string|object $event) use ($container): void {
            $this->handler->handler->invokeArgs(
                $container->get(
                    $this->handler
                        ->handler
                        ->getDeclaringClass()
                        ->getName(),
                ),
                [$event],
            );
        };
    }
}
