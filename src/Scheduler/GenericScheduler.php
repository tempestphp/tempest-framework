<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use Tempest\Console\ConsoleCommand;

final class GenericScheduler implements Scheduler
{
    public const string CACHE_PATH = __DIR__ . '/last-schedule-run.cache.php';

    public function __construct(
        private SchedulerConfig $config,
    ) {
    }

    public function run(?DateTime $date = null): void
    {
        $date ??= new DateTime();

        $commands = $this->getCommandsToRunAt($date);

        foreach ($commands as $command) {
            $this->execute($command);
        }
    }

    private function execute(ConsoleCommand $scheduledCommand): void
    {
        $command = $this->compileCommand($scheduledCommand);

        exec($command);
    }

    private function compileCommand(ConsoleCommand $commandDefinition): string
    {
        return join(' ', [
            '(',
            $this->config->path,
            $commandDefinition->getName(),
            ')',
            $this->config->outputMode->value,
            $this->config->output,
            ($this->config->runInBackground ? '&' : ''),
        ]);
    }

    /**
     * @param DateTime $date
     *
     * @return array
     */
    private function getCommandsToRunAt(DateTime $date): array
    {
        $previousRuns = $this->getPreviousRuns();

        $eligibleToRun = array_filter(
            $this->config->schedules,
            fn (ConsoleCommand $command) => $this->canCommandRunAt(
                command: $command,
                now: $date,
                lastRunTimestamp: $previousRuns[$command->getName()] ?? null,
            )
        );

        $this->markCommandsAsRan($eligibleToRun, $date);

        return $eligibleToRun;
    }

    /**
     * Returns a key value array of the last run time of each command.
     * The key is the command name and the value is the last run time in unix timestamp.
     *
     * @return array<string, int>
     */
    private function getPreviousRuns(): array
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
    private function markCommandsAsRan(array $eligibleToRun, DateTime $ranAt): void
    {
        $lastRuns = $this->getPreviousRuns();

        foreach ($eligibleToRun as $command) {
            $lastRuns[$command->getName()] = $ranAt->getTimestamp();
        }

        file_put_contents(self::CACHE_PATH, serialize($lastRuns));
    }

    private function canCommandRunAt(ConsoleCommand $command, DateTime $now, ?int $lastRunTimestamp): bool
    {
        if ($lastRunTimestamp === null) {
            return true;
        }

        $interval = $command->schedule->interval;

        $secondsInterval = (int) $interval->format('s');

        return $now->getTimestamp() - $lastRunTimestamp >= $secondsInterval;
    }
}
