<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use Attribute;
use DateInterval;

#[Attribute(Attribute::TARGET_METHOD)]
final class Schedule
{
    public function __construct(
        public readonly DateInterval $interval,
    ) {
    }
}
