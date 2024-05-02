<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

class NullInvocationExecutor implements ScheduledInvocationExecutor
{
    public function execute(string $compiledCommand): void
    {

    }
}
