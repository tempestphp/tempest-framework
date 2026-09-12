<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use DateTimeImmutable;
use Deprecated;
use Symfony\Component\Process\Process;
use Tempest\CommandBus\AsyncCommandRepositories\RedisCommandRepository;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;

use function Tempest\Support\arr;

if (class_exists(ConsoleCommand::class)) {
    final readonly class MonitorAsyncCommands
    {
        use HasConsole;

        public function __construct(
            private CommandRepository $repository,
            private ConsoleArgumentBag $argumentBag,
            private Console $console,
        ) {}

        #[ConsoleCommand(name: 'command:monitor', description: 'Monitors and executes pending async commands')]
        public function __invoke(): void
        {
            $this->migrateStoredCommands();

            $this->info('Monitoring for new commands. Press <em>Ctrl+C</em> to stop.');
            $this->writeln();

            /** @var \Symfony\Component\Process\Process[] $processes */
            $processes = [];

            while (true) { // @phpstan-ignore-line
                foreach ($processes as $uuid => $process) {
                    if (! $process->isTerminated()) {
                        continue;
                    }

                    if ($process->isSuccessful()) {
                        $this->console->keyValue(
                            key: "<style='fg-gray'>{$uuid}</style>",
                            value: "<style='fg-green bold'>SUCCESS</style>",
                        );
                    } else {
                        $this->console->keyValue(
                            key: "<style='fg-gray'>{$uuid}</style>",
                            value: "<style='fg-red bold'>FAILED</style>",
                        );
                    }

                    $output = trim($process->getOutput());

                    if ($output !== '' && $output !== '0') {
                        $this->writeln($output);
                    }

                    $errorOutput = trim($process->getErrorOutput());

                    if ($errorOutput !== '' && $errorOutput !== '0') {
                        $this->writeln($errorOutput);
                    }

                    unset($processes[$uuid]);
                }

                $availableCommands = arr($this->repository->getPendingCommands())
                    ->filter(fn (object $_, string $uuid) => ! array_key_exists($uuid, $processes));

                if (count($processes) === 5) {
                    $this->sleep(0.5);

                    continue;
                }

                if ($availableCommands->isEmpty()) {
                    $this->sleep(0.5);

                    continue;
                }

                // Start a task
                $uuid = $availableCommands->keys()->first();

                $time = new DateTimeImmutable();
                $this->console->keyValue(
                    key: $uuid,
                    value: "<style='fg-gray'>{$time->format('Y-m-d H:i:s')}</style>",
                );

                $process = new Process([
                    $this->argumentBag->getBinaryPath(),
                    $this->argumentBag->getCliName(),
                    'command:handle',
                    $uuid,
                ], getcwd());

                $process->start();

                $processes[$uuid] = $process;
            }
        }

        /**
         * Moves Redis commands stored by an older version over, on the first start after the upgrade.
         */
        #[Deprecated(message: 'Remove in 4.0.')]
        private function migrateStoredCommands(): void
        {
            if (! $this->repository instanceof RedisCommandRepository) {
                return;
            }

            $migrated = $this->repository->migrateStoredCommands();

            if ($migrated === 0) {
                return;
            }

            $this->info("Migrated <em>{$migrated}</em> stored commands to the current storage format.");
        }

        private function sleep(float $seconds): void
        {
            usleep((int) ($seconds * 1_000_000));
        }
    }
}
