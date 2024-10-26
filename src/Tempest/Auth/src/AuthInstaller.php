<?php

namespace Tempest\Auth;

use Tempest\Core\DoNotDiscover;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Generation\ClassManipulator;
use function Tempest\src_namespace;
use function Tempest\src_path;

final readonly class AuthInstaller implements Installer
{
    use PublishesFiles;

    public function getName(): string
    {
        return 'auth';
    }

    public function publishFiles(): void
    {
        $this->publish(
            source: __DIR__ . 'User.php',
            destination: src_path('User.php'),
            callback: function (string $source, string $destination): string {
                (new ClassManipulator($source))
                    ->setNamespace(src_namespace())
                    ->removeClassAttribute(DoNotDiscover::class)
                    ->save($destination);
            }
        );

//        $this->publish(
//            source: __DIR__ . 'UserPermission.php',
//            destination: src_path('UserPermission.php'),
//        );
//
//        $this->publish(
//            source: __DIR__ . 'CreateUsersTable.php',
//            destination: src_path('CreateUsersTable.php'),
//        );
//
//        $this->publish(
//            source: __DIR__ . 'CreateUserPermissionsTable.php',
//            destination: src_path('CreateUserPermissionsTable.php'),
//        );
    }
}