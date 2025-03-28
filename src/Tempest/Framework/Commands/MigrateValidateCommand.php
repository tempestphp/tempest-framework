<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Container\Singleton;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationValidationFailed;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class MigrateValidateCommand
{
    private bool $validationPassed = true;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
    ) {}

    #[ConsoleCommand(
        name: 'migrate:validate',
        description: 'Validates the integrity of existing migration files by checking if they have been tampered with.',
    )]
    public function __invoke(): ExitCode
    {
        $this->console->info('Validating migration files...');

        $this->migrationManager->validate();

        if (! $this->validationPassed) {
            return ExitCode::ERROR;
        }

        $this->console
            ->success('Migration files are valid')
            ->writeln();

        return ExitCode::SUCCESS;
    }

    #[EventHandler]
    public function onMigrationValidationFailed(MigrationValidationFailed $event): void
    {
        $this->console->error(
            "Migration file '{$event->name}' failed validation: {$event->exception->getMessage()}",
        );

        $this->validationPassed = false;
    }
}
