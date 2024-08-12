<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\ClassReflector;

final class ScheduleDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private readonly SchedulerConfig $schedulerConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $schedule = $method->getAttribute(Schedule::class);

            if ($schedule === null) {
                continue;
            }

            $command = $method->getAttribute(ConsoleCommand::class);

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
