<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

enum Permission: int
{
    case FULL = 0777;

    // Owner permissions
    case OWNER_EXECUTE = 0o100;
    case OWNER_WRITE = 0o200;
    case OWNER_WRITE_EXECUTE = 0o300;
    case OWNER_READ = 0o400;
    case OWNER_READ_EXECUTE = 0o500;
    case OWNER_READ_WRITE = 0o600;
    case OWNER_ALL = 0o700;

    // Group permissions
    case GROUP_EXECUTE = 0o010;
    case GROUP_WRITE = 0o020;
    case GROUP_WRITE_EXECUTE = 0o030;
    case GROUP_READ = 0o040;
    case GROUP_READ_EXECUTE = 0o050;
    case GROUP_READ_WRITE = 0o060;
    case GROUP_ALL = 0o070;

    // Others permissions
    case OTHERS_EXECUTE = 0o001;
    case OTHERS_WRITE = 0o002;
    case OTHERS_WRITE_EXECUTE = 0o003;
    case OTHERS_READ = 0o004;
    case OTHERS_READ_EXECUTE = 0o005;
    case OTHERS_READ_WRITE = 0o006;
    case OTHERS_ALL = 0o007;

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

    public function without(Permission ...$permissions): int
    {
        return array_reduce(
            $permissions,
            fn ($carry, Permission $permission) => $carry & ~$permission->value,
            $this->value
        );
    }
}
