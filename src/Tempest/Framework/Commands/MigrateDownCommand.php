<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Singleton;
use Tempest\Database\Migrations\MigrationFailed;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationRolledBack;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class MigrateDownCommand
{
    private int $count = 0;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
    ) {
    }

    #[ConsoleCommand(
        name: 'migrate:down',
        description: 'Rollbacks all executed migrations',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(): void
    {
        $this->migrationManager->down();

        $this->console->success(sprintf("Rolled back %s migrations", $this->count));
    }

    #[EventHandler]
    public function onMigrationRolledBack(MigrationRolledBack $event): void
    {
        $this->console->writeln("- Rollback {$event->name}");
        $this->count += 1;
    }
}
