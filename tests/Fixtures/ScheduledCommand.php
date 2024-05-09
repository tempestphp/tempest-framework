<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Scheduler\Every;
use Tempest\Console\Scheduler\Schedule;

final readonly class ScheduledCommand
{
    public function __construct(
        private Console $console,
    ) {
    }

    #[Schedule(Every::SECOND)]
    #[ConsoleCommand('scheduled')]
    public function command(): void
    {
        $this->console->writeln('A command got scheduled');
    }

    #[Schedule(Every::SECOND)]
    public function method(): void
    {
        $this->console->writeln('A method got scheduled');
    }
}
