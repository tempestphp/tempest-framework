<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Installer;

use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Migrations\MigrationManager;

use function Tempest\root_path;
use function Tempest\src_path;
use function Tempest\Support\Namespace\to_fqcn;

final class DatabaseSessionInstaller implements Installer
{
    use PublishesFiles;

    private(set) string $name = 'sessions:database';

    public function __construct(
        private readonly MigrationManager $migrationManager,
        private readonly Container $container,
        private readonly Console $console,
        private readonly ConsoleArgumentBag $consoleArgumentBag,
    ) {}

    public function install(): void
    {
        $migration = $this->publish(
            source: __DIR__ . '/CreateSessionsTable.stub.php',
            destination: src_path('Sessions/CreateSessionsTable.php'),
        );

        $this->publish(
            source: __DIR__ . '/session.config.stub.php',
            destination: src_path('Sessions/session.config.php'),
        );

        $this->publishImports();

        if ($migration && $this->shouldMigrate()) {
            $this->migrationManager->executeUp(
                migration: $this->container->get(to_fqcn($migration, root: root_path())),
            );
        }
    }

    private function shouldMigrate(): bool
    {
        $argument = $this->consoleArgumentBag->get('migrate');

        if ($argument === null || ! is_bool($argument->value)) {
            return $this->console->confirm('Do you want to execute migrations?', default: false);
        }

        return (bool) $argument->value;
    }
}
