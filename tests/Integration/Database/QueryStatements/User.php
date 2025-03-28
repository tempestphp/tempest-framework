<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\IsDatabaseModel;

final class User
{
    use IsDatabaseModel;

    public string $name;

    public string $email;
}
