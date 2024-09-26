<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Scheduler;
use Tempest\Console\Scheduler\ScheduledInvocationRan;
use Tempest\EventBus\EventHandler;

final readonly class ScheduleRunCommand
{
    use HasConsole;

    public function __construct(
        private Scheduler $scheduler,
        private Console $console,
    ) {
    }

    #[ConsoleCommand('schedule:run')]
    public function __invoke(): void
    {
        $this->scheduler->run();

        $this->success('Done');
    }

    #[EventHandler]
    public function onScheduledInvocationRan(ScheduledInvocationRan $invocation): void
    {
        $this->writeln(sprintf(
            "<em>%s</em> completed",
            $invocation->invocation->getCommandName()
        ));
    }
}
