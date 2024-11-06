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
            foreach ($processes as $key => $process) {
                $errorOutput = trim($process->getErrorOutput());

                $time = new DateTimeImmutable();

                if ($errorOutput) {
                    $this->error($errorOutput);
                    $this->writeln("<error>{$key}</error> failed at {$time->format('Y-m-d H:i:s')}");
                    unset($processes[$key]);
                } elseif ($process->isTerminated()) {
                    $this->writeln("<success>{$key}</success> finished at {$time->format('Y-m-d H:i:s')}");
                    unset($processes[$key]);
                }
            }

            $availableUuids = arr($this->repository->available())
                ->filter(fn (string $uuid) => ! in_array($uuid, array_keys($processes)));

            if (count($processes) === 5) {
                sleep(1);

                continue;
            }

            if ($availableUuids->isEmpty()) {
                sleep(1);

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
}
