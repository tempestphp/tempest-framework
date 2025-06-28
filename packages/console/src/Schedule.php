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
        /**
         * Interval at which the scheduled task should be executed.
         */
        Interval|Every $interval,

        /**
         * Where to send STDOUT output.
         */
        public string $output = '/dev/null',

        /**
         * Whether to append or overwrite the output.
         */
        public OutputMode $outputMode = OutputMode::APPEND,

        /**
         * Whether to run the task in the background.
         */
        public bool $runInBackground = true,
    ) {
        $this->interval = ($interval instanceof Interval) ? $interval : $interval->toInterval();
    }
}
