<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use Tempest\Console\Commands\ScheduleTaskCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Support\Reflection\MethodReflector;

final readonly class ScheduledInvocation
{
    public function __construct(
        public Schedule $schedule,
        public ConsoleCommand|MethodReflector $handler,
    ) {
    }

    public function getCommandName(): string
    {
        if ($this->handler instanceof MethodReflector) {
            return ScheduleTaskCommand::NAME
                . ' '
                . str_replace('\\', '\\\\', $this->handler->getDeclaringClass()->getName())
                . '::'
                . $this->handler->getName();
        }

        return $this->handler->getName();
    }

    public function canRunAt(DateTime $date, ?int $lastRunTimestamp = null): bool
    {
        if ($lastRunTimestamp === null) {
            return true;
        }

        $secondsInterval = $this->schedule->interval->inSeconds();

        return $date->getTimestamp() - $lastRunTimestamp >= $secondsInterval;
    }
}
