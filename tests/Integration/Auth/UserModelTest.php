<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Auth\Install\Permission;
use Tempest\Auth\Install\PermissionMigration;
use Tempest\Auth\Install\User;
use Tempest\Auth\Install\UserMigration;
use Tempest\Auth\Install\UserPermissionMigration;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Integration\Auth\Fixtures\UserPermissionBackedEnum;
use Tests\Tempest\Integration\Auth\Fixtures\UserPermissionUnitEnum;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class UserModelTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate(
            CreateMigrationsTable::class,
            UserMigration::class,
            PermissionMigration::class,
            UserPermissionMigration::class,
        );
    }

    public function test_grant_permission_string(): void
    {
        $user = (new User(
            name: 'Brent',
            email: 'brendt@stitcher.io',
        ))
            ->setPassword('password')
            ->save()
            ->grantPermission('admin');

        $this->assertTrue($user->hasPermission('admin'));
        $this->assertFalse($user->hasPermission('guest'));
    }

    public function test_grant_permission_backed_enum(): void
    {
        $user = (new User(
            name: 'Brent',
            email: 'brendt@stitcher.io',
        ))
            ->setPassword('password')
            ->save()
            ->grantPermission(UserPermissionBackedEnum::ADMIN);

        $this->assertTrue($user->hasPermission(UserPermissionBackedEnum::ADMIN));
        $this->assertFalse($user->hasPermission(UserPermissionBackedEnum::GUEST));
    }

    public function test_grant_permission_unit_enum(): void
    {
        $user = (new User(
            name: 'Brent',
            email: 'brendt@stitcher.io',
        ))
            ->setPassword('password')
            ->save()
            ->grantPermission(UserPermissionUnitEnum::ADMIN);

        $this->assertTrue($user->hasPermission(UserPermissionUnitEnum::ADMIN));
        $this->assertFalse($user->hasPermission(UserPermissionUnitEnum::GUEST));
    }

    public function test_grant_permission_model(): void
    {
        $permission = (new Permission('admin'))->save();

        $user = (new User(
            name: 'Brent',
            email: 'brendt@stitcher.io',
        ))
            ->setPassword('password')
            ->save()
            ->grantPermission($permission);

        $this->assertTrue($user->hasPermission($permission));
        $this->assertTrue($user->hasPermission('admin'));
        $this->assertFalse($user->hasPermission('guest'));
    }

    public function test_revoke_permission(): void
    {
        $user = (new User(
            name: 'Brent',
            email: 'brendt@stitcher.io',
        ))
            ->setPassword('password')
            ->save()
            ->grantPermission(UserPermissionBackedEnum::ADMIN)
            ->grantPermission(UserPermissionBackedEnum::GUEST)
            ->revokePermission(UserPermissionBackedEnum::ADMIN);

        $this->assertFalse($user->hasPermission(UserPermissionBackedEnum::ADMIN));
        $this->assertTrue($user->hasPermission(UserPermissionBackedEnum::GUEST));
    }
}
