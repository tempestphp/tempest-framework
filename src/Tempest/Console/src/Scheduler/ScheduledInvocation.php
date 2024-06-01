<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use ReflectionMethod;
use Tempest\Console\Commands\ScheduleTaskCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;

final readonly class ScheduledInvocation
{
    public function __construct(
        public Schedule $schedule,
        public ConsoleCommand|ReflectionMethod $handler,
    ) {
    }

    public function getCommandName(): string
    {
        if ($this->handler instanceof ReflectionMethod) {
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

    public function __serialize(): array
    {
        $data = [
            'schedule' => $this->schedule,
        ];

        if ($this->handler instanceof ReflectionMethod) {
            $data['handler_class'] = $this->handler->getDeclaringClass()->getName();
            $data['handler_method'] = $this->handler->getName();
        } else {
            $data['handler'] = $this->handler;
        }

        return $data;
    }

    public function __unserialize(array $data): void
    {
        $this->schedule = $data['schedule'];

        if (isset($data['handler_class'])) {
            $this->handler = new ReflectionMethod(
                objectOrMethod: $data['handler_class'],
                method: $data['handler_method'],
            );
        } else {
            $this->handler = $data['handler'];
        }
    }
}
