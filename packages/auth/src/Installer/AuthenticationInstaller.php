<?php

declare(strict_types=1);

namespace Tempest\Auth\Installer;

use Tempest\Container\Container;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Migrations\MigrationManager;

use function Tempest\src_path;

final class AuthenticationInstaller
{
    use PublishesFiles;

    public function __construct(
        private readonly MigrationManager $migrationManager,
        private readonly Container $container,
    ) {}

    #[Installer('Authentication', alias: 'auth')]
    public function install(?bool $migrate = null, ?bool $oauth = null): void
    {
        $migration = $this->publish(__DIR__ . '/basic-user/CreateUsersTableMigration.stub.php', src_path('Authentication/CreateUsersTable.php'));
        $this->publish(__DIR__ . '/basic-user/UserModel.stub.php', src_path('Authentication/User.php'));
        $this->publish(__DIR__ . '/basic-user/MustBeAuthenticatedMiddleware.stub.php', src_path('Authentication/MustBeAuthenticatedMiddleware.php'));
        $this->publish(__DIR__ . '/basic-user/MustBeAuthenticated.stub.php', src_path('Authentication/MustBeAuthenticated.php'));
        $this->publishImports();

        if ($migration && $this->shouldMigrate($migrate)) {
            $this->migrationManager->up();
        }

        if ($this->shouldInstallOAuth($oauth)) {
            $this->container->get(OAuthInstaller::class)->install();
        }
    }

    private function shouldMigrate(?bool $migrate): bool
    {
        if (is_bool($migrate)) {
            return $migrate;
        }

        return $this->console->confirm('Do you want to execute migrations?', default: false);
    }

    private function shouldInstallOAuth(?bool $oauth): bool
    {
        if (is_bool($oauth)) {
            return $oauth;
        }

        return $this->console->confirm('Do you want to install OAuth?', default: false);
    }
}
