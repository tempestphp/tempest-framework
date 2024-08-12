<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Tempest\Console\Console;
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
    ) {
    }

    #[ConsoleCommand(
        name: 'migrate:fresh',
        description: 'Drop all tables and rerun migrations from scratch',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(): ExitCode
    {
        $this->console->info('Dropping tables…');

        $this->migrationManager->dropAll();

        $this->console
            ->success(sprintf("Dropped %s tables", $this->count))
            ->writeln();

        $this->console->info('Migrate up…');

        return $this->console->call('migrate:up');
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
