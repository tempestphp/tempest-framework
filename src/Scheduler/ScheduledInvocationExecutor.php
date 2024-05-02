<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

interface ScheduledInvocationExecutor
{
    public function execute(string $compiledCommand): void;
}
