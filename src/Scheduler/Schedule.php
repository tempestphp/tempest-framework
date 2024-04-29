<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateInterval;

final class Schedule
{
    public function __construct(
        public readonly DateInterval $interval,
    ) {
    }
}
