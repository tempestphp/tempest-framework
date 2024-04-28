<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use Tempest\Console\ConsoleCommand;

interface ScheduledCommandsResolver
{
    /**
     * @return ConsoleCommand[]
     */
    public function resolve(DateTime $date): array;
}
