<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class User
{
    use IsDatabaseModel;

    public string $name;

    public string $email;
}
