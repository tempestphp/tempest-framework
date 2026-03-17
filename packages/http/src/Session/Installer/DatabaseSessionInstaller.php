<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Installer;

use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Migrations\MigrationManager;

use function Tempest\src_path;

final class DatabaseSessionInstaller implements Installer
{
    use PublishesFiles;

    private(set) string $name = 'sessions:database';

    public function __construct(
        private readonly MigrationManager $migrationManager,
        private readonly Console $console,
        private readonly ConsoleArgumentBag $consoleArgumentBag,
    ) {}

    public function install(): void
    {
        $migration = $this->publish(
            source: __DIR__ . '/CreateSessionsTable.php',
            destination: src_path('Sessions/CreateSessionsTable.php'),
        );

        $this->publish(
            source: __DIR__ . '/session.config.stub.php',
            destination: src_path('Sessions/session.config.php'),
        );

        $this->publishImports();

        if ($migration && $this->shouldMigrate()) {
            $this->migrationManager->up();
        }
    }

    private function shouldMigrate(): bool
    {
        $argument = $this->consoleArgumentBag->get('migrate');

        if (! $argument instanceof ConsoleInputArgument || ! is_bool($argument->value)) {
            return $this->console->confirm('Do you want to execute migrations?');
        }

        return (bool) $argument->value;
    }
}
