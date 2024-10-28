<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use function Tempest\src_path;

final readonly class AuthInstaller implements Installer
{
    use PublishesFiles;

    public function getName(): string
    {
        return 'auth';
    }

    public function install(): void
    {
        $publishFiles = [
            __DIR__ . '/User.php' => src_path('User.php'),
            __DIR__ . '/UserMigration.php' => src_path('UserMigration.php'),
            __DIR__ . '/Permission.php' => src_path('Permission.php'),
            __DIR__ . '/PermissionMigration.php' => src_path('PermissionMigration.php'),
            __DIR__ . '/UserPermission.php' => src_path('UserPermission.php'),
            __DIR__ . '/UserPermissionMigration.php' => src_path('UserPermissionMigration.php'),
        ];

        foreach ($publishFiles as $source => $destination) {
            $this->publish(
                source: $source,
                destination: $destination,
            );
        }
    }
}
