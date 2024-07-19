<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Database\Migrations\MigrationFailed;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationRolledBack;
use Tempest\EventBus\EventHandler;

final class MigrateDownCommand
{
    private static int $count = 0;

    public function __construct(
        readonly private Console $console,
        readonly private MigrationManager $migrationManager,
    ) {
    }

    #[ConsoleCommand(
        name: 'migrate:down',
        description: 'Rollbacks all executed migrations',
        middleware: [CautionMiddleware::class],
    )]
    public function __invoke(): void
    {
        $this->migrationManager->down();

        $this->console->success("Done");
        $this->console->writeln(sprintf("Rolled back %s migrations", self::$count));
    }

    #[EventHandler]
    public function onMigrationRolledBack(MigrationRolledBack $event): void
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
