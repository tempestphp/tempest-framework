<?php

namespace Tempest\Process;

use Closure;
use Countable;
use Tempest\DateTime\Duration;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Arr\MutableArray;

final class InvokedProcessPool implements Countable
{
    /**
     * All running processes in the pool.
     */
    public ImmutableArray $running {
        get => $this->processes
            ->toImmutableArray()
            ->filter(fn (InvokedProcess $process) => $process->running);
    }

    /**
     * All processes in the pool.
     *
     * @var ImmutableArray<InvokedProcess>
     */
    public ImmutableArray $all {
        get => $this->processes->toImmutableArray();
    }

    public function __construct(
        /** @var MutableArray<InvokedProcessInterface> */
        private MutableArray $processes,
    ) {}

    /**
     * Send a signal to each running process in the pool.
     */
    public function signal(int $signal): ImmutableArray
    {
        return $this->running->each(fn (InvokedProcess $process) => $process->signal($signal));
    }

    /**
     * Stops all processes that are currently running.
     */
    public function stop(float|int|Duration $timeout = 10, ?int $signal = null): ImmutableArray
    {
        return $this->running->each(fn (InvokedProcess $process) => $process->stop($timeout, $signal));
    }

    /**
     * Waits for all processes in the pool to finish and returns their results.
     */
    public function wait(): ProcessPoolResults
    {
        return new ProcessPoolResults(
            $this->all->map(fn (InvokedProcess $process) => $process->wait()),
        );
    }

    /**
     * Iterates over each running process in the pool and applies the given callback.
     */
    public function forEachRunning(\Closure $callback): self
    {
        $this->running->each(fn (InvokedProcess $process) => $callback($process));

        return $this;
    }

    /**
     * Iterates over each invoked process in the pool and applies the given callback.
     */
    public function forEach(\Closure $callback): self
    {
        $this->processes->each(fn (InvokedProcess $process) => $callback($process));

        return $this;
    }

    public function count(): int
    {
        return $this->processes->count();
    }
}
