<?php

namespace Tempest\Process\Testing;

use Tempest\Process\InvokedProcess;
use Tempest\Process\PendingPool;
use Tempest\Process\PendingProcess;
use Tempest\Process\Pool;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessPoolResults;
use Tempest\Process\ProcessResult;

final class RestrictedProcessExecutor implements ProcessExecutor
{
    public function run(array|string|PendingProcess $command): ProcessResult
    {
        throw ProcessExecutionWasForbidden::forPendingProcess($command);
    }

    public function start(array|string|PendingProcess $command): InvokedProcess
    {
        throw ProcessExecutionWasForbidden::forPendingProcess($command);
    }

    public function pool(iterable $pool): Pool
    {
        throw ProcessExecutionWasForbidden::forPendingPool($pool);
    }

    public function concurrently(iterable $pool): ProcessPoolResults
    {
        throw ProcessExecutionWasForbidden::forPendingPool($pool);
    }
}
