<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Scheduler;
use Tempest\Core\ShellExecutor;

use function Tempest\event;
use function Tempest\internal_storage_path;

final readonly class GenericScheduler implements Scheduler
{
    public function __construct(
        private SchedulerConfig $config,
        private ConsoleArgumentBag $argumentBag,
        private ShellExecutor $executor,
    ) {
    }

    public static function getCachePath(): string
    {
        return internal_storage_path('scheduler', 'last-schedule-run.cache.php');
    }

    public function run(?DateTime $date = null): void
    {
        $date ??= new DateTime();

        $commands = $this->getInvocationsToRun($date);

        foreach ($commands as $command) {
            $this->execute($command);

            event(new ScheduledInvocationRan($command));
        }
    }

    private function execute(ScheduledInvocation $invocation): void
    {
        $command = $this->compileInvocation($invocation);

        $this->executor->execute($command);
    }

    private function compileInvocation(ScheduledInvocation $invocation): string
    {
        $binary = $this->argumentBag->getBinaryPath() . ' ' . $this->argumentBag->getCliName();

        return implode(' ', [
            '(' . $binary,
            $invocation->getCommandName() . ')',
            $invocation->schedule->outputMode->value,
            $invocation->schedule->output,
            $invocation->schedule->runInBackground ? '&' : '',
        ]);
    }

    /** @return \Tempest\Console\Scheduler\ScheduledInvocation[] */
    private function getInvocationsToRun(DateTime $date): array
    {
        $previousRuns = $this->getPreviousRuns();

        $eligibleToRun = array_filter(
            $this->config->scheduledInvocations,
            fn (ScheduledInvocation $invocation) => $invocation->canRunAt(
                date: $date,
                lastRunTimestamp: $previousRuns[$invocation->handler->getName()] ?? null,
            ),
        );

        $this->markInvocationsAsRun($eligibleToRun, $date);

        return $eligibleToRun;
    }

    /**
     * Returns a key value array of the last run time of each invocation.
     * The key is the invocation name and the value is the last run time in unix timestamp.
     *
     * @return array<string, int>
     */
    private function getPreviousRuns(): array
    {
        if (! file_exists(self::getCachePath())) {
            return [];
        }

        return unserialize(file_get_contents(self::getCachePath()), ['allowed_classes' => false]);
    }

    /** @param ScheduledInvocation[] $ranInvocations */
    private function markInvocationsAsRun(array $ranInvocations, DateTime $ranAt): void
    {
        $lastRuns = $this->getPreviousRuns();

        foreach ($ranInvocations as $invocation) {
            $lastRuns[$invocation->handler->getName()] = $ranAt->getTimestamp();
        }

        $directory = dirname(self::getCachePath());

        if (! is_dir($directory)) {
            mkdir(directory: $directory, recursive: true);
        }

        file_put_contents(self::getCachePath(), serialize($lastRuns));
    }
}
