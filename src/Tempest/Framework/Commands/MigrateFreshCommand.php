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
        bool $validate = false,
    ): ExitCode {
        if ($validate) {
            $validationSuccess = $this->console->call(MigrateValidateCommand::class);

            if ($validationSuccess !== 0 && $validationSuccess !== ExitCode::SUCCESS) {
                return ExitCode::INVALID;
            }
        }

        $this->console->info('Dropping tables…');

        $this->migrationManager->dropAll();

        $this->console
            ->success(sprintf('Dropped %s tables', $this->count))
            ->writeln();

        $this->console->info('Migrate up…');

        return $this->console->call(MigrateUpCommand::class);
    }

    #[EventHandler]
    public function onTableDropped(TableDropped $event): void
    {
        $this->console->writeln("- Dropped {$event->name}");
        $this->count += 1;
    }

    #[EventHandler]
    public function onFreshMigrationFailed(FreshMigrationFailed $event): void
    {
        $this->console->error($event->throwable->getMessage());
    }
}
