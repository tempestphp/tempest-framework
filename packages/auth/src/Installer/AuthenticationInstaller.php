<?php

declare(strict_types=1);

namespace Tempest\Auth\Installer;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Migrations\MigrationManager;

use function Tempest\src_path;

if (class_exists(ConsoleCommand::class)) {
    final class AuthenticationInstaller implements Installer
    {
        use PublishesFiles;

        private(set) string $name = 'auth';

        public function __construct(
            private readonly MigrationManager $migrationManager,
            private readonly Container $container,
            private readonly Console $console,
            private readonly ConsoleArgumentBag $consoleArgumentBag,
        ) {}

        public function install(): void
        {
            $migration = $this->publish(__DIR__ . '/basic-user/CreateUsersTableMigration.stub.php', src_path('Authentication/CreateUsersTable.php'));
            $this->publish(__DIR__ . '/basic-user/UserModel.stub.php', src_path('Authentication/User.php'));
            $this->publishImports();

            if ($migration && $this->shouldMigrate()) {
                $this->migrationManager->up();
            }

            if ($this->shouldInstallOAuth()) {
                $this->container->get(OAuthInstaller::class)->install();
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

        private function shouldInstallOAuth(): bool
        {
            $argument = $this->consoleArgumentBag->get('oauth');

            if ($argument === null || ! is_bool($argument->value)) {
                return $this->console->confirm('Do you want to install OAuth?', default: false);
            }

            return (bool) $argument->value;
        }
    }
}
