<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use DateTimeImmutable;

interface Scheduler
{
    public function run(?DateTime $date = null): void;
}
