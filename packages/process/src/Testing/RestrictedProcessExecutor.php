<?php

namespace Tempest\Process\Testing;

use Tempest\Process\InvokedSystemProcess;
use Tempest\Process\PendingProcess;
use Tempest\Process\Pool;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessPoolResults;
use Tempest\Process\ProcessResult;

final class RestrictedProcessExecutor implements ProcessExecutor
{
    public function run(string|PendingProcess $command): ProcessResult
    {
        throw ProcessExecutionWasForbidden::forPendingProcess($command);
    }

    public function start(string|PendingProcess $command): InvokedSystemProcess
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
