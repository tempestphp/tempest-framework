<?php

declare(strict_types=1);

namespace Tempest\Auth\Install;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class UserPermission implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public User $user,
        public Permission $permission,
    ) {}
}
