<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\EventBus\EventBus;
use Throwable;

final readonly class QueryEventDispatcher
{
    public function __construct(
        private EventBus $eventBus,
    ) {}

    public function dispatch(QueryExecuted $event): void
    {
        try {
            $this->eventBus->dispatch($event);
        } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
        }
    }
}
