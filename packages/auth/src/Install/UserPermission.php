<?php

declare(strict_types=1);

namespace Tempest\Auth\Install;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class UserPermission
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public User $user;

    public Permission $permission;
}
