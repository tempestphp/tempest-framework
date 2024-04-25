<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutput;

final class GenericScheduler implements Scheduler
{
    private string $path;

    public function __construct(
        private ConsoleConfig $config,
        private ConsoleOutput $output,
    ) {
        $this->path = "php tempest"; // todo: discover path
    }

    public function run(): void
    {
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
        $commands = $this->config->scheduledCommands;

        return $commands;
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
