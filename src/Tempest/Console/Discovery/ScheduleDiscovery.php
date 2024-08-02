<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\Attributes;

final class ScheduleDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private SchedulerConfig $schedulerConfig,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $schedule = Attributes::find(Schedule::class)->in($method)->first();

            if ($schedule === null) {
                continue;
            }

            $command = Attributes::find(ConsoleCommand::class)->in($method)->first();

            if ($command) {
                $this->schedulerConfig->addCommandInvocation($method, $command, $schedule);
            } else {
                $this->schedulerConfig->addMethodInvocation($method, $schedule);
            }
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->schedulerConfig->scheduledInvocations);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $scheduledInvocations = unserialize($payload);

        $this->schedulerConfig->scheduledInvocations = $scheduledInvocations;
    }
}
