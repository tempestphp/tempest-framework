<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use Tempest\Console\ConsoleCommand;

final class GenericScheduler implements Scheduler
{
    public function __construct(
        private SchedulerConfig $config,
    ) {
    }

    public function run(?DateTime $date = null): void
    {
        $date ??= new DateTime();
        $startTime = $date->getTimestamp();  // Get the timestamp at the start of script execution
        $secondsToNextMinute = 60 - (int) $date->format("s");  // Calculate the seconds until the start of the next minute
        $endTime = $startTime + $secondsToNextMinute;  // Calculate the end time at the start of the next minute

        while (time() < $endTime) {  // Run until the start of the next minute
            $currentDate = new DateTime();  // Update current time for each loop iteration

            $commands = $this->config->resolver->resolve($currentDate);

            foreach ($commands as $command) {
                $this->execute($command);
            }

            // Sleep until the start of the next second to maintain second-level precision
            time_sleep_until(time() + 1);
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
}
