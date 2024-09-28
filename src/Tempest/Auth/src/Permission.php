<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class Permission implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
    ) {
    }
}
