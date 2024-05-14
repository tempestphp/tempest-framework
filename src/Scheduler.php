<?php

declare(strict_types=1);

namespace Tempest\Console;

use DateTime;

interface Scheduler
{
    public function run(?DateTime $date = null): void;
}
