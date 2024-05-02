<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\Schedule;

final class ScheduledCommand
{
    #[Schedule(Every::Second)]
    public function handler(): void
    {
    }

    #[Schedule(Every::Second)]
    #[ConsoleCommand('scheduledededed')]
    public function command(): void
    {

    }
}
