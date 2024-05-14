<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Scheduler;

final readonly class SchedulerRunCommand
{
    public function __construct(
        private Scheduler $scheduler,
    ) {
    }

    #[ConsoleCommand('schedule:run')]
    public function __invoke(): void
    {
        $this->scheduler->run();
    }
}
