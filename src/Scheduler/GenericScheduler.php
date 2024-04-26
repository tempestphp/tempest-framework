<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DatePeriod;
use DateTime;
use DateInterval;
use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutput;

final class GenericScheduler implements Scheduler
{
    private string $path;
    private DateTime $dayStart;
    private DateTime $now;

    public function __construct(
        private ConsoleConfig $config,
        private ConsoleOutput $output,
    ) {
        $this->path = "php tempest"; // todo: discover path
    }

    public function run(?DateTime $date = null): void
    {
        $now = $date ?? (new DateTime());

        $this->dayStart = (clone $now)->setTime(0, 0, 0);
        $this->now = ($now)->setTime(
            (int) $now->format('H'),
            (int) $now->format('i'),
        );

        $commands = $this->getCommandsToRun();

        foreach ($commands as $command) {
            $this->execute($command);
        }
    }

    private function execute(ConsoleCommand $commandDefinition): void
    {
        $name = $commandDefinition->getName();
        $this->output->writeln("Running command: {$name}");

        $command = $this->buildCommand($commandDefinition);

        exec($command);

        $this->output->writeln("Command finished: {$name}");
    }

    private function getCommandsToRun(): array
    {
        $eligibleCommands = [];
        $commands = $this->config->scheduledCommands;

        $currentDayOfMonth = (int) $this->now->format('j');
        $currentMonthOfYear = (int) $this->now->format('n');
        $minutesSinceStartOfDay = (int)$this->now->format('H') * 60 + (int)$this->now->format('i');

        foreach ($commands as $command) {
            $interval = $command->cron->interval;
            $shouldRun = false;

            if ($interval->m > 0) {
                // Assuming the task should run on the first day of each month
                if ($currentDayOfMonth == 1) {
                    $shouldRun = true;
                }
            } elseif ($interval->d > 0) {
                // Daily schedules
                $daysSinceStart = $this->now->diff($this->dayStart)->days;
                if ($daysSinceStart % $interval->d === 0) {
                    $shouldRun = true;
                }
            } elseif ($interval->h > 0 || $interval->i > 0) {
                // Hourly or minutely schedules
                $totalMinutes = $interval->h * 60 + $interval->i;
                if ($minutesSinceStartOfDay % $totalMinutes === 0) {
                    $shouldRun = true;
                }
            }

            if ($shouldRun) {
                $eligibleCommands[] = $command;
            }
        }

        return $eligibleCommands;
    }

    private function buildCommand(ConsoleCommand $commandDefinition): string
    {
        $cronDefinition = $commandDefinition->cron;

        $compiled = $this->compileCommand($commandDefinition);

        $logRedirect = $cronDefinition->output
            ? sprintf("%s %s", $cronDefinition->outputType->value, $cronDefinition->output)
            : '';

        return trim(
            sprintf("(%s) %s 2>&1 %s", $compiled, $logRedirect, $cronDefinition->runInBackground ? '&' : '')
        );
    }

    private function compileCommand(ConsoleCommand $commandDefinition): string
    {
        $name = $commandDefinition->getName();

        return $this->path . ' ' . $name;
    }
}
