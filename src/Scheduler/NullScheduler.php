<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use DateTimeImmutable;

final class NullScheduler implements Scheduler
{
    public function run(DateTime|null $date = null): void
    {

    }
}
