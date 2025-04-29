<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Reflection\MethodReflector;

final class SchedulerConfig
{
    public function __construct(
        /** @var ScheduledInvocation[] $scheduledInvocations */
        public array $scheduledInvocations = [],
    ) {}

    public function addMethodInvocation(MethodReflector $handler, Schedule $schedule): self
    {
        $this->scheduledInvocations[] = new ScheduledInvocation($schedule, $handler);

        return $this;
    }

    public function addCommandInvocation(MethodReflector $handler, ConsoleCommand $command, Schedule $schedule): self
    {
        $command->setHandler($handler);

        $this->scheduledInvocations[] = new ScheduledInvocation($schedule, $command);

        return $this;
    }
}
