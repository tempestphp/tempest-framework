<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateUserDatabaseMigration implements MigratesUp
{
    private(set) string $name = '0000-01-01_create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->varchar('name');
    }
}
