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
use Tempest\Support\Reflection\Attributes;

final class ScheduleDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/../../../../.cache/tempest/schedule-discovery.cache.php';

    public function __construct(private SchedulerConfig $schedulerConfig)
    {
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

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->schedulerConfig->scheduledInvocations));
    }

    public function restoreCache(Container $container): void
    {
        $scheduledInvocations = unserialize(file_get_contents(self::CACHE_PATH));

        $this->schedulerConfig->scheduledInvocations = $scheduledInvocations;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
