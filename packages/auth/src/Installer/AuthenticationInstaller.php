<?php

declare(strict_types=1);

namespace Tempest\Auth\Installer;

use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Migrations\MigrationManager;

use function Tempest\root_path;
use function Tempest\src_path;
use function Tempest\Support\Namespace\to_fqcn;

if (class_exists(\Tempest\Console\ConsoleCommand::class)) {
    final class AuthenticationInstaller implements Installer
    {
        use PublishesFiles;

        public bool $installOAuth = false;

        private(set) string $name = 'auth';

        public function __construct(
            private readonly MigrationManager $migrationManager,
            private readonly Container $container,
            private readonly Console $console,
            private readonly ConsoleArgumentBag $consoleArgumentBag,
        ) {}

        public function install(): void
        {
            // First question, ask whether to also install OAuth, as it changes the stubs to publish
            $this->installOAuth = $this->shouldInstallOAuth();

            // Get the appropriate stubs
            $stubPath = $this->installOAuth ? 'oauth' : 'basic-user';

            // Publish the stubs
            $migration = $this->publish(__DIR__ . "/{$stubPath}/CreateUsersTableMigration.stub.php", src_path('Authentication/CreateUsersTable.php'));
            $this->publish(__DIR__ . "/{$stubPath}/UserModel.stub.php", src_path('Authentication/User.php'));
            $this->publish(__DIR__ . '/basic-user/MustBeAuthenticated.stub.php', src_path('Authentication/MustBeAuthenticated.php'));
            $this->publish(__DIR__ . '/basic-user/LoginController.stub.php', src_path('Authentication/LoginController.php'));
            $this->publishImports();

            // Offer to migrate
            if ($migration && $this->shouldMigrate()) {
                $this->migrationManager->executeUp(
                    migration: $this->container->get(to_fqcn($migration, root: root_path())),
                );
            }

            // Run the OAuth installer now
            if ($this->installOAuth) {
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
