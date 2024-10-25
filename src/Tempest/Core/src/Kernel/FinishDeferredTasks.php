<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use Tempest\Core\DeferredTasks;

final readonly class FinishDeferredTasks
{
    public function __construct(
        private DeferredTasks $deferredTasks,
    ) {
    }

    public function __invoke(): void
    {
        foreach ($this->deferredTasks->getTasks() as $task) {
            $task();
        }
    }
}
