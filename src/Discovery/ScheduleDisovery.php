<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Support\Reflection\Attributes;

final class ScheduleDisovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/schedule-discovery.cache.php';

    public function __construct(private SchedulerConfig $schedulerConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $command = Attributes::find(ConsoleCommand::class)->in($method)->first();

            if (! $command || ! $command->schedule) {
                continue;
            }

            $this->schedulerConfig->addSchedule($method, $command);
        }
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->schedulerConfig->schedules));
    }

    public function restoreCache(Container $container): void
    {
        $schedules = unserialize(file_get_contents(self::CACHE_PATH));

        $this->schedulerConfig->schedules = $schedules;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
