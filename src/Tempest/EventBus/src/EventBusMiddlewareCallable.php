<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Closure;

final readonly class EventBusMiddlewareCallable
{
    public function __construct(
        private Closure $closure,
    ) {}

    public function __invoke(string|object $event): void
    {
        ($this->closure)($event);
    }
}
