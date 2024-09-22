<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Filesystem\Permission;

/**
 * @internal
 */
final class PermissionTest extends TestCase
{
    #[DataProvider('permissionDataProvider')]
    public function test_permission_combinations(int $expected, int $actual): void
    {
        $this->assertSame(decoct($expected), decoct($actual));
    }

    public function test_combining_permissions(): void
    {
        $permissions = Permission::allow(
            Permission::OWNER_WRITE,
            Permission::OWNER_READ,
            Permission::OWNER_EXECUTE,
            Permission::GROUP_READ,
        );

        $this->assertSame(decoct(0740), decoct($permissions));
    }

    public function test_permissions_with_other_permissions(): void
    {
        $permissions = Permission::OWNER_WRITE->with(
            Permission::OWNER_READ,
            Permission::OWNER_EXECUTE,
        );

        $this->assertSame(decoct(0700), decoct($permissions));
    }

    public static function permissionDataProvider(): array
    {
        return [
            [0777, Permission::fullAccess()],
            [0700, Permission::ownerFull()],
            [0744, Permission::ownerFullGroupReadOthersRead()],
            [0444, Permission::readOnly()],
        ];
    }
}
