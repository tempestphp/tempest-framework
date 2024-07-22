<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;

final readonly class ScheduledCommand
{
    public function __construct(
        private Console $console,
    ) {
    }

    #[Schedule(Every::MINUTE)]
    #[ConsoleCommand('scheduled')]
    public function command(): void
    {
        $this->console->writeln('A command got scheduled');
    }

    #[Schedule(Every::MINUTE)]
    public function method(): void
    {
        $this->console->writeln('A method got scheduled');
    }
}
