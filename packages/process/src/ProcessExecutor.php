<?php

namespace Tempest\Process;

interface ProcessExecutor
{
    /**
     * Runs the given process.
     *
     * @param string[]|string|PendingProcess $command
     */
    public function run(array|string|PendingProcess $command): ProcessResult;

    /**
     * Invokes the given process asynchronously.
     *
     * @param string[]|string|PendingProcess $command
     */
    public function start(array|string|PendingProcess $command): InvokedProcess;

    /**
     * Returns a pool of processes, which can be executed.
     *
     * @param iterable<PendingProcess|string> $pool
     */
    public function pool(iterable $pool): Pool;

    /**
     * Executes a pool of processes concurrently and returns the results.
     *
     * @param iterable<PendingProcess|string> $pool
     */
    public function concurrently(iterable $pool): ProcessPoolResults;
}
