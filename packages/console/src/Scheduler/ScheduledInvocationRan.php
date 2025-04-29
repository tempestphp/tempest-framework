<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

final readonly class ScheduledInvocationRan
{
    public function __construct(
        public ScheduledInvocation $invocation,
    ) {}
}
