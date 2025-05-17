<?php

declare(strict_types=1);

namespace Tempest\Auth\Install;

use Tempest\Database\IsDatabaseModel;

final class UserPermission
{
    use IsDatabaseModel;

    public User $user;

    public Permission $permission;
}
