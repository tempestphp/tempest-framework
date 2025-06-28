<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use Tempest\Container\Container;
use Tempest\Core\DeferredTasks;

final readonly class FinishDeferredTasks
{
    public function __construct(
        private DeferredTasks $deferredTasks,
        private Container $container,
    ) {}

    public function __invoke(): void
    {
        foreach ($this->deferredTasks->getTasks() as $name => $task) {
            $this->container->invoke($task);
            $this->deferredTasks->forget($name);
        }
    }
}
