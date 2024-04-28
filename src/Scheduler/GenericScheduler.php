<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use DateTime;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;

final class GenericScheduler implements Scheduler
{
    public function __construct(
        private SchedulerConfig $config,
        private ConsoleOutput $output,
        private ScheduledCommandsResolver $resolver,
    ) {
    }

    public function run(?DateTime $date = null): void
    {
        $date ??= new DateTime();

        $commands = $this->resolver->resolve($date);

        foreach ($commands as $command) {
            $this->execute($command);
        }
    }

    private function execute(ConsoleCommand $scheduledCommand): void
    {
        $name = $scheduledCommand->getName();
        $this->output->writeln("Running command: $name");

        $command = $this->compileCommand($scheduledCommand);

        exec($command);

        $this->output->writeln("Command finished: $name");
    }

    private function compileCommand(ConsoleCommand $commandDefinition): string
    {
        $name = $commandDefinition->getName();

        return '(' . $this->config->path . ' ' . $name . ')' . ' ' . $this->config->outputMode->value .  ' ' . $this->config->output . ' 2>&1 &';
    }
}
