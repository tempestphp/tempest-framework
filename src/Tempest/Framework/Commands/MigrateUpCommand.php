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
    public function __invoke(
        #[ConsoleArgument(description: 'Validates the integrity of existing migration files by checking if they have been tampered with.')]
        bool $validate = true,
    ): ExitCode {
        if ($validate) {
            $validationSuccess = $this->console->call(MigrateValidateCommand::class);

            if ($validationSuccess !== 0 && $validationSuccess !== ExitCode::SUCCESS) {
                return ExitCode::INVALID;
            }
        }

        $this->migrationManager->up();

        $this->console
            ->success(sprintf('Migrated %s migrations', $this->count));

        return ExitCode::SUCCESS;
    }

    #[EventHandler]
    public function onMigrationMigrated(MigrationMigrated $event): void
    {
        $this->console->writeln("- {$event->name}");
        $this->count += 1;
    }
}
