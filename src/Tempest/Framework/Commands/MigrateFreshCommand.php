<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Singleton;
use Tempest\Database\Migrations\FreshMigrationFailed;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\TableDropped;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class MigrateFreshCommand
{
    private int $count = 0;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
    ) {}

    #[ConsoleCommand(
        name: 'migrate:fresh',
        description: 'Drops all tables and rerun migrations from scratch',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Validates the integrity of existing migration files by checking if they have been tampered with.')]
        bool $validate = true,
        #[ConsoleArgument(description: 'Use a specific database.')]
        ?string $database = null,
        #[ConsoleArgument(description: 'Run database seeders after the database has been migrated')]
        bool $seed = false,
        #[ConsoleArgument(description: 'Run all database seeders after the database has been migrated')]
        bool $all = false,
        #[ConsoleArgument(description: 'Select one specific seeder to run')]
        ?string $seeder = null,
    ): ExitCode {
        if ($validate) {
            $validationSuccess = $this->console->call(MigrateValidateCommand::class);

            if ($validationSuccess !== 0 && $validationSuccess !== ExitCode::SUCCESS) {
                return ExitCode::INVALID;
            }
        }

        $this->console->header('Dropping tables');
        $this->migrationManager->onDatabase($database)->dropAll();

        if ($this->count === 0) {
            $this->console->info('There is no migration to drop.');
        }

        $this->console->call(MigrateUpCommand::class, [
            'fresh' => false,
            'validate' => false,
            'database' => $database,
        ]);

        $seed = $seed || $seeder !== null;

        if ($seed) {
            $this->console->header('Seeding database');
            $this->console->call(DatabaseSeedCommand::class, [
                'database' => $database,
                'all' => $all,
                'seeder' => $seeder,
            ]);
        }

        return ExitCode::SUCCESS;
    }

    #[EventHandler]
    public function onTableDropped(TableDropped $event): void
    {
        $this->count += 1;
        $this->console->keyValue(
            key: "<style='fg-gray'>{$event->name}</style>",
            value: "<style='fg-green'>DROPPED</style>",
        );
    }

    #[EventHandler]
    public function onFreshMigrationFailed(FreshMigrationFailed $event): void
    {
        $this->console->error($event->throwable->getMessage());
    }
}
