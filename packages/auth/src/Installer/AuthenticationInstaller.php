<?php

declare(strict_types=1);

namespace Tempest\Auth\Installer;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;

use function Tempest\src_path;

final class AuthenticationInstaller implements Installer
{
    use PublishesFiles;

    private(set) string $name = 'auth';

    public function install(): void
    {
        $publishFiles = [
            __DIR__ . '/basic-user/StubUserModel.php' => src_path('Authentication/User.php'),
            __DIR__ . '/basic-user/StubCreateUsersTableMigration.php' => src_path('Authentication/CreateUsersTable.php'),
        ];

        foreach ($publishFiles as $source => $destination) {
            $this->publish($source, $destination);
        }

        $this->publishImports();
    }
}
