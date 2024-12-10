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

        $this->assertSame(decoct(0o740), decoct($permissions));
    }

    public function test_permissions_with_other_permissions(): void
    {
        $permissions = Permission::OWNER_WRITE->with(
            Permission::OWNER_READ,
            Permission::OWNER_EXECUTE,
        );

        $this->assertSame(decoct(0o700), decoct($permissions));
    }

    public function test_permissions_without_other_permissions(): void
    {
        $permissions = Permission::OWNER_ALL->without(Permission::OWNER_READ);

        $this->assertSame(decoct(0o300), decoct($permissions));
    }

    public function test_checking_whether_permission_has_other_permission(): void
    {
        $permissions = 0o755;

        $this->assertFalse(Permission::has($permissions, Permission::GROUP_WRITE));
        $this->assertTrue(Permission::has($permissions, Permission::GROUP_READ_EXECUTE));
    }

    public function test_empty_allow_returns_zero(): void
    {
        $this->assertSame(0, Permission::allow());
    }

    public static function permissionDataProvider(): array
    {
        return [
            [0o100, Permission::OWNER_EXECUTE],
            [0o200, Permission::OWNER_WRITE],
            [0o300, Permission::OWNER_WRITE_EXECUTE],
            [0o400, Permission::OWNER_READ],
            [0o500, Permission::OWNER_READ_EXECUTE],
            [0o600, Permission::OWNER_READ_WRITE],
            [0o700, Permission::OWNER_ALL],

            [0o010, Permission::GROUP_EXECUTE],
            [0o020, Permission::GROUP_WRITE],
            [0o030, Permission::GROUP_WRITE_EXECUTE],
            [0o040, Permission::GROUP_READ],
            [0o050, Permission::GROUP_READ_EXECUTE],
            [0o060, Permission::GROUP_READ_WRITE],
            [0o070, Permission::GROUP_ALL],

            [0o001, Permission::OTHERS_EXECUTE],
            [0o002, Permission::OTHERS_WRITE],
            [0o003, Permission::OTHERS_WRITE_EXECUTE],
            [0o004, Permission::OTHERS_READ],
            [0o005, Permission::OTHERS_READ_EXECUTE],
            [0o006, Permission::OTHERS_READ_WRITE],
            [0o007, Permission::OTHERS_ALL],

            [0o777, Permission::OWNER_ALL->with(Permission::GROUP_ALL, Permission::OTHERS_ALL)],
            [0o700, Permission::OWNER_ALL],
            [0o744, Permission::OWNER_ALL->with(Permission::GROUP_READ, Permission::OTHERS_READ)],
            [0o444, Permission::OWNER_READ->with(Permission::GROUP_READ, Permission::OTHERS_READ)],

            [0o744, Permission::FULL->without(Permission::GROUP_WRITE_EXECUTE, Permission::OTHERS_WRITE_EXECUTE)],
        ];
    }
}
