<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\Interval;
use Tempest\Console\Scheduler\OutputMode;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Schedule
{
    public Interval $interval;

    public function __construct(
        Interval|Every $interval,
        public string $output = '/dev/null',
        public OutputMode $outputMode = OutputMode::Append,
        public bool $runInBackground = true,
    ) {
        $this->interval = ($interval instanceof Interval) ? $interval : $interval->toInterval();
    }
}
