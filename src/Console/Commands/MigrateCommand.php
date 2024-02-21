<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationMigrated;
use Tempest\Events\EventHandler;

final readonly class MigrateCommand
{
    public function __construct(
        private Console $console,
        private MigrationManager $migrationManager
    ) {
    }

    #[ConsoleCommand(
        name: 'migrate',
        description: 'Run all new migrations',
    )]
    public function __invoke(): void
    {
        $this->migrationManager->up();

        $this->console->success("Done");
    }

    #[EventHandler]
    public function onMigrationMigrated(MigrationMigrated $event): void
    {
        $this->console->writeln("- {$event->name}");
    }
}
