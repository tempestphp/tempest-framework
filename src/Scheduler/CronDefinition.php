<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateInterval;

final class CronDefinition
{
    public function __construct(
        public readonly DateInterval $interval,
        public readonly bool $runInBackground = false,
        public readonly ?string $output = null,
        public readonly OutputType $outputType = OutputType::Append,
    ) {
    }
}
