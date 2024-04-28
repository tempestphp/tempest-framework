<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use InvalidArgumentException;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;

final class SchedulerConfig
{
    public function __construct(
        public string $path = "php tempest",
        public string $output = "/dev/null",
        public OutputMode $outputMode = OutputMode::Append,

        /** @var ConsoleCommand[] $schedules */
        public array $schedules = [],
    ) {
    }

    public function addSchedule(ReflectionMethod $handler, ConsoleCommand $command): self
    {
        if (! $command->schedule) {
            throw new InvalidArgumentException('Command does not have a schedule');
        }

        $command->setHandler($handler);

        $this->schedules[] = $command;

        /**
         * Sort the schedules by the interval in ascending order.
         * This allows us to prioritize the schedules that run more frequently.
         */
        usort($this->schedules, function (ConsoleCommand $a, ConsoleCommand $b) {
            return $a->schedule->interval->format('s') <=> $b->schedule->interval->format('s');
        });

        return $this;
    }
}
