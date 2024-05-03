<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;

final readonly class ScheduledInvocation
{
    public function __construct(
        public Schedule $schedule,
        public Invocation $invocation,
    ) {

    }

    public function canRunAt(DateTime $date, ?int $lastRunTimestamp = null): bool
    {
        if ($lastRunTimestamp === null) {
            return true;
        }

        $secondsInterval = $this->schedule->interval->inSeconds();

        return $date->getTimestamp() - $lastRunTimestamp >= $secondsInterval;
    }
}
