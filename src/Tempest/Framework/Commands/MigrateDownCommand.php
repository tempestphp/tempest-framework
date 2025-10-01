<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Singleton;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationRolledBack;
use Tempest\EventBus\EventBus;

#[Singleton]
final class MigrateDownCommand
{
    private int $count = 0;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
        private readonly EventBus $eventBus,
    ) {}

    #[ConsoleCommand(
        name: 'migrate:down',
        description: 'Rollbacks all executed migrations',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Use a specific database.')]
        ?string $database = null,
    ): void {
        $this->eventBus->listen(function (MigrationRolledBack $event): void {
            $this->count += 1;
            $this->console->keyValue(
                key: "<style='fg-gray'>{$event->name}</style>",
                value: "<style='fg-green'>ROLLED BACK</style>",
            );
        });

        $this->console->header('Migrating');
        $this->migrationManager->onDatabase($database)->down();

        if ($this->count === 0) {
            $this->console->info('There is no migration to roll back.');
        }
    }
}
