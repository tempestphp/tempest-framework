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
        public bool $runInBackground = true,

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

        return $this;
    }
}
