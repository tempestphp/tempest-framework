<?php

namespace Tempest\Process;

use Tempest\Support\Arr\ImmutableArray;

final class Pool
{
    public function __construct(
        /** @var ImmutableArray<PendingProcess> */
        private ImmutableArray $pendingProcesses,
        private ProcessExecutor $processExecutor,
    ) {}

    /**
     * Gets all pending processes.
     */
    public function processes(): ImmutableArray
    {
        return $this->pendingProcesses;
    }

    /**
     * Start all pending processes in the pool.
     */
    public function start(): InvokedProcessPool
    {
        $processes = $this->pendingProcesses
            ->map(fn (PendingProcess $pending) => $this->processExecutor->start($pending))
            ->toMutableArray();

        return new InvokedProcessPool($processes);
    }

    /**
     * Starts all pending processes in the pool and wait for them to finish.
     */
    public function run(): ProcessPoolResults
    {
        return $this->start()->wait();
    }
}
