<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Singleton;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationMigrated;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class MigrateUpCommand
{
    private int $count = 0;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
    ) {}

    #[ConsoleCommand(
        name: 'migrate:up',
        description: 'Runs all new migrations',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(): void
    {
        $this->migrationManager->up();

        $this->console
            ->success(sprintf('Migrated %s migrations', $this->count));
    }

    #[EventHandler]
    public function onMigrationMigrated(MigrationMigrated $event): void
    {
        $this->console->writeln("- {$event->name}");
        $this->count += 1;
    }
}
