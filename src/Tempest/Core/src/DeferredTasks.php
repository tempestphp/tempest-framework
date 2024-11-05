<?php

declare(strict_types=1);

namespace Tempest\Core;

use Closure;
use Tempest\Container\Singleton;

#[Singleton]
final class DeferredTasks
{
    private array $tasks = [];

    public function add(Closure $task): void
    {
        $this->tasks[] = $task;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }
}
