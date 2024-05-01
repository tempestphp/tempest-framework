<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\Schedule;
use Tempest\Console\Scheduler\Interval;

final class ScheduledCommand
{

    public function __construct(
        protected ConsoleOutput $output,
    )
    {

    }

    #[Schedule(Every::Second)]
    #[ConsoleCommand('scheduled')]
    public function command(): void
    {
        $this->output->writeln('A command got scheduled');
    }

    #[Schedule(new Interval(minutes: 5))]
    public function method(): void
    {
        $this->output->writeln('A method got scheduled');
    }

}
