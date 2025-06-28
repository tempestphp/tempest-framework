<?php

declare(strict_types=1);

namespace Tempest\Core;

use Closure;
use Tempest\Container\Singleton;
use Tempest\Support\Arr;
use Tempest\Support\Random;

#[Singleton]
final class DeferredTasks
{
    /** @var array<string,Closure> */
    private array $tasks = [];

    /**
     * Adds a deferred task to the list of tasks. Optionally, specify a name for uniqueness.
     */
    public function add(Closure $task, ?string $name = null): void
    {
        $this->tasks[$name ?? Random\secure_string(10)] = $task;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Forgets the given deferred task.
     */
    public function forget(string $name): void
    {
        Arr\forget_keys($this->tasks, $name);
    }
}
