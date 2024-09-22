<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

enum Permission: int
{
    // Owner permissions
    case OWNER_READ = 0o400;
    case OWNER_WRITE = 0o200;
    case OWNER_EXECUTE = 0o100;

    // Group permissions
    case GROUP_READ = 0o040;
    case GROUP_WRITE = 0o020;
    case GROUP_EXECUTE = 0o010;

    // Others permissions
    case OTHERS_READ = 0o004;
    case OTHERS_WRITE = 0o002;
    case OTHERS_EXECUTE = 0o001;

    public static function allow(Permission ...$permissions): int
    {
        if (empty($permissions)) {
            return 0;
        }

        return $permissions[0]->with(...$permissions);
    }

    public function with(Permission ...$permissions): int
    {
        return array_reduce(
            $permissions,
            fn ($carry, Permission $permission) => $carry | $permission->value,
            $this->value
        );
    }

    public static function fullAccess(): int
    {
        return self::allow(
            Permission::OWNER_WRITE,
            Permission::OWNER_READ,
            Permission::OWNER_EXECUTE,
            Permission::GROUP_WRITE,
            Permission::GROUP_READ,
            Permission::GROUP_EXECUTE,
            Permission::OTHERS_WRITE,
            Permission::OTHERS_READ,
            Permission::OTHERS_EXECUTE
        );
    }

    public static function readOnly(): int
    {
        return self::allow(
            Permission::OWNER_READ,
            Permission::GROUP_READ,
            Permission::OTHERS_READ,
        );
    }

    public static function ownerFull(): int
    {
        return self::allow(
            Permission::OWNER_WRITE,
            Permission::OWNER_READ,
            Permission::OWNER_EXECUTE,
        );
    }

    public static function ownerFullGroupReadOthersRead(): int
    {
        return self::ownerFull() | self::GROUP_READ->value | self::OTHERS_READ->value;
    }
}
