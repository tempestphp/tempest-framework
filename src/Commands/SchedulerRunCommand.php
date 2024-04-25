<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Scheduler\Scheduler;

final class SchedulerRunCommand
{
    private DateTimeImmutable $startedAt;

    public function __construct(
        private Scheduler $scheduler,
    ) {
        $this->startedAt = new DateTimeImmutable();
    }

    #[ConsoleCommand('scheduler:run')]
    public function __invoke(): void
    {
        $this->scheduler->run();
    }
}
