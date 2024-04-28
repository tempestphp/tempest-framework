<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use Tempest\Console\ConsoleCommand;

final class StatefulScheduledCommandsResolver implements ScheduledCommandsResolver
{
    public const string CACHE_PATH = __DIR__ . '/last-schedule-run.cache.php';

    public function __construct(
        private SchedulerConfig $config,
    ) {
    }

    public function resolve(DateTime $date): array
    {
        $lastRuns = $this->get();

        $eligibleToRun = array_filter(
            $this->config->schedules,
            fn (ConsoleCommand $command) => $this->isCommandEligible(
                command: $command,
                now: $date,
                lastRunTimestamp: $lastRuns[$command->getName()] ?? null,
            )
        );

        $this->markAsRan($eligibleToRun, $date);

        return $eligibleToRun;
    }

    /**
     * Returns a key value array of the last run time of each command.
     * The key is the command name and the value is the last run time in unix timestamp.
     *
     * @return array<string, int>
     */
    public function get(): array
    {
        if (! file_exists(self::CACHE_PATH)) {
            return [];
        }

        return unserialize(file_get_contents(self::CACHE_PATH));
    }

    /**
     * @param ConsoleCommand[] $eligibleToRun
     * @param DateTime $ranAt
     *
     * @return void
     */
    public function markAsRan(array $eligibleToRun, DateTime $ranAt): void
    {
        $lastRuns = $this->get();

        foreach ($eligibleToRun as $command) {
            $lastRuns[$command->getName()] = $ranAt->getTimestamp();
        }

        file_put_contents(self::CACHE_PATH, serialize($lastRuns));
    }

    private function isCommandEligible(ConsoleCommand $command, DateTime $now, ?int $lastRunTimestamp): bool
    {
        if ($lastRunTimestamp === null) {
            return true;
        }

        $interval = $command->schedule->interval;

        $secondsInterval = (int) $interval->format('s');

        return $now->getTimestamp() - $lastRunTimestamp >= $secondsInterval;
    }
}
