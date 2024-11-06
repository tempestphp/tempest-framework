<?php

namespace Tempest\CommandBus;

use DateTimeImmutable;
use Symfony\Component\Process\Process;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use function Tempest\Support\arr;

final readonly class MonitorAsyncCommands
{
    use HasConsole;

    public function __construct(
        private AsyncCommandRepository $repository,
        private Console $console,
    ) {}

    #[ConsoleCommand(name: 'command:monitor')]
    public function __invoke(): void
    {
        $this->success("Monitoring for new commands. Press ctrl+c to stop.");

        /** @var \Symfony\Component\Process\Process[] $processes */
        $processes = [];

        while (true) {
            foreach ($processes as $key => $process) {
                $errorOutput = trim($process->getErrorOutput());

                if ($errorOutput) {
                    $this->error($errorOutput);
                }

                if ($process->isTerminated()) {
                    $this->success("{$key} finished, {$process->getExitCode()}");
                    unset($processes[$key]);
                }
            }

            $uuids = arr($this->repository->all())
                ->filter(fn (string $uuid) => ! in_array($uuid, array_keys($processes)));

            if (count($processes) === 5) {
                sleep(1);
                continue;
            }

            if ($uuids->isEmpty()) {
                sleep(1);
                continue;
            }

            // Start a task
            $uuid = $uuids->first();
            $time = new DateTimeImmutable();
            $this->info("{$uuid} started at {$time->format('Y-m-d H:i:s')}");
            $process = new Process(['php', 'tempest', 'command:handle', $uuid], getcwd());
            $process->start();
            $processes[$uuid] = $process;
        }
    }
}