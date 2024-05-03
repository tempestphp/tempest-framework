<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

final class GenericInvocationExecutor implements ScheduledInvocationExecutor
{
    public function execute(string $compiledCommand): void
    {
        shell_exec($compiledCommand);
    }
}
