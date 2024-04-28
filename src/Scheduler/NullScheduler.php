<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;

final class NullScheduler implements Scheduler
{
    public function run(DateTime|null $date = null): void
    {

    }
}
