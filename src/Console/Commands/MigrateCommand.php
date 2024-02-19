<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Interface\Console;

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

    // TODO: add events for writing to the console which migrations have run
}
