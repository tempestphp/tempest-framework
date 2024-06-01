<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;

final class SchedulerConfig
{
    public function __construct(
        public string $path = "php tempest",

        /** @var ScheduledInvocation[] $scheduledInvocations */
        public array $scheduledInvocations = [],
    ) {
    }

    public function addMethodInvocation(ReflectionMethod $handler, Schedule $schedule): self
    {
        $this->scheduledInvocations[] = new ScheduledInvocation($schedule, $handler);

        return $this;
    }

    public function addCommandInvocation(ReflectionMethod $handler, ConsoleCommand $command, Schedule $schedule): self
    {
        $command->setHandler($handler);

        $this->scheduledInvocations[] = new ScheduledInvocation($schedule, $command);

        return $this;
    }
}
