<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Container\Singleton;
use Tempest\Database\Migrations\MigrationFileWasMissing;
use Tempest\Database\Migrations\MigrationHashMismatched;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationValidationFailed;
use Tempest\EventBus\EventBus;

#[Singleton]
final class MigrateValidateCommand
{
    private bool $validationPassed = true;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
        private readonly EventBus $eventBus,
    ) {}

    #[ConsoleCommand(
        name: 'migrate:validate',
        description: 'Validates the integrity of existing migration files by checking if they have been tampered with.',
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Use a specific database.')]
        ?string $database = null,
    ): ExitCode {
        $this->eventBus->listen($this->onMigrationValidationFailed(...));
        $this->console->header('Validating migration files');
        $this->migrationManager->onDatabase($database)->validate();

        if (! $this->validationPassed) {
            $this->console->writeln();
            $this->console->error('Migration files are invalid.');

            return ExitCode::ERROR;
        }

        $this->console->success('Migration files are valid.');

        return ExitCode::SUCCESS;
    }

    public function onMigrationValidationFailed(MigrationValidationFailed $event): void
    {
        $error = match ($event->exception::class) {
            MigrationHashMismatched::class => 'Hash mismatch',
            MigrationFileWasMissing::class => 'Missing file',
            default => 'Unknown error',
        };

        $this->console->keyValue(
            key: "<style='fg-gray'>{$event->name}</style>",
            value: "<style='fg-red'>" . strtoupper($error) . '</style>',
        );

        $this->validationPassed = false;
    }
}
