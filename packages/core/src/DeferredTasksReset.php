<?php

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\Resettable;

final readonly class DeferredTasksReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        $this->container->unregister(DeferredTasks::class);
    }
}
