<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use function Tempest\src_path;

final class AuthInstaller implements Installer
{
    use PublishesFiles;

    private(set) public string $name = 'auth';

    public function install(): void
    {
        $publishFiles = [
            __DIR__ . '/Install/User.php' => src_path('Auth/User.php'),
            __DIR__ . '/Install/UserMigration.php' => src_path('Auth/UserMigration.php'),
            __DIR__ . '/Install/Permission.php' => src_path('Auth/Permission.php'),
            __DIR__ . '/Install/PermissionMigration.php' => src_path('Auth/PermissionMigration.php'),
            __DIR__ . '/Install/UserPermission.php' => src_path('Auth/UserPermission.php'),
            __DIR__ . '/Install/UserPermissionMigration.php' => src_path('Auth/UserPermissionMigration.php'),
        ];

        foreach ($publishFiles as $source => $destination) {
            $this->publish(
                source: $source,
                destination: $destination,
            );
        }

        $this->publishImports();
    }
}
