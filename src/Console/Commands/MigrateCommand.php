<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Database\Migrations\MigrationFailed;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationMigrated;
use Tempest\Events\EventHandler;

final class MigrateCommand
{
    private static int $count = 0;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
    ) {
    }

    #[ConsoleCommand(
        name: 'migrate:up',
        aliases: ['haha'],
        description: 'Run all new migrations',
        isDangerous: true,
        help: [
            'This command will run all new migrations.',
            'This command is dangerous and can cause data loss.',
        ]
    )]
    public function __invoke(): void
    {
        $this->migrationManager->up();

        $this->console->success("Done");
        $this->console->writeln(sprintf("Migrated %s migrations", self::$count));
    }

    #[EventHandler]
    public function onMigrationMigrated(MigrationMigrated $event): void
    {
        $this->console->writeln("- {$event->name}");
        self::$count += 1;
    }

    #[EventHandler]
    public function onMigrationFailed(MigrationFailed $event): void
    {
        $this->console->error(sprintf("Error while executing migration: %s", $event->name ?? 'command'));
        $this->console->error($event->exception->getMessage());
    }
}
