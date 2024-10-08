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
    public function test_permission_combinations(int $expected, Permission|int $actual): void
    {
        if ($actual instanceof Permission) {
            $actual = $actual->value;
        }

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

    public function test_permissions_without_other_permissions(): void
    {
        $permissions = Permission::OWNER_ALL->without(Permission::OWNER_READ);

        $this->assertSame(decoct(0300), decoct($permissions));
    }

    public function test_checking_whether_permission_has_other_permission(): void
    {
        $permissions = 0755;

        $this->assertFalse(Permission::has($permissions, Permission::GROUP_WRITE));
        $this->assertTrue(Permission::has($permissions, Permission::GROUP_READ_EXECUTE));
    }

    public static function permissionDataProvider(): array
    {
        return [
            [0100, Permission::OWNER_EXECUTE],
            [0200, Permission::OWNER_WRITE],
            [0300, Permission::OWNER_WRITE_EXECUTE],
            [0400, Permission::OWNER_READ],
            [0500, Permission::OWNER_READ_EXECUTE],
            [0600, Permission::OWNER_READ_WRITE],
            [0700, Permission::OWNER_ALL],

            [0010, Permission::GROUP_EXECUTE],
            [0020, Permission::GROUP_WRITE],
            [0030, Permission::GROUP_WRITE_EXECUTE],
            [0040, Permission::GROUP_READ],
            [0050, Permission::GROUP_READ_EXECUTE],
            [0060, Permission::GROUP_READ_WRITE],
            [0070, Permission::GROUP_ALL],

            [0001, Permission::OTHERS_EXECUTE],
            [0002, Permission::OTHERS_WRITE],
            [0003, Permission::OTHERS_WRITE_EXECUTE],
            [0004, Permission::OTHERS_READ],
            [0005, Permission::OTHERS_READ_EXECUTE],
            [0006, Permission::OTHERS_READ_WRITE],
            [0007, Permission::OTHERS_ALL],

            [0777, Permission::OWNER_ALL->with(Permission::GROUP_ALL, Permission::OTHERS_ALL)],
            [0700, Permission::OWNER_ALL],
            [0744, Permission::OWNER_ALL->with(Permission::GROUP_READ, Permission::OTHERS_READ)],
            [0444, Permission::OWNER_READ->with(Permission::GROUP_READ, Permission::OTHERS_READ)],

            [0744, Permission::FULL->without(Permission::GROUP_WRITE_EXECUTE, Permission::OTHERS_WRITE_EXECUTE)],
        ];
    }
}
