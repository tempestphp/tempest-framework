<?php

declare(strict_types=1);

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
    ) {
    }

    #[ConsoleCommand(name: 'command:monitor')]
    public function __invoke(): void
    {
        $this->success("Monitoring for new commands. Press ctrl+c to stop.");

        /** @var \Symfony\Component\Process\Process[] $processes */
        $processes = [];

        while (true) { // @phpstan-ignore-line
            foreach ($processes as $uuid => $process) {
                $time = new DateTimeImmutable();

                if ($process->isTerminated()) {
                    if ($process->isSuccessful()) {
                        $this->writeln("<success>{$uuid}</success> finished at {$time->format('Y-m-d H:i:s')}");
                    } else {
                        $this->writeln("<error>{$uuid}</error> failed at {$time->format('Y-m-d H:i:s')}");
                    }

                    if ($output = trim($process->getOutput())) {
                        $this->writeln($output);
                    }

                    if ($errorOutput = trim($process->getErrorOutput())) {
                        $this->writeln($errorOutput);
                    }

                    unset($processes[$uuid]);
                }
            }

            $availableUuids = arr($this->repository->getPendingUuids())
                ->filter(fn (string $uuid) => ! in_array($uuid, array_keys($processes)));

            if (count($processes) === 5) {
                $this->sleep(0.5);

                continue;
            }

            if ($availableUuids->isEmpty()) {
                $this->sleep(0.5);

                continue;
            }

            // Start a task
            $uuid = $availableUuids->first();
            $time = new DateTimeImmutable();
            $this->writeln("<h2>{$uuid}</h2> started at {$time->format('Y-m-d H:i:s')}");
            $process = new Process(['php', 'tempest', 'command:handle', $uuid], getcwd());
            $process->start();
            $processes[$uuid] = $process;
        }
    }

    private function sleep(float $seconds): void
    {
        usleep((int) ($seconds * 1_000_000));
    }
}
