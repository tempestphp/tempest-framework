<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreateUserDatabaseMigration implements DatabaseMigration
{
    private(set) string $name = '0000-01-01_create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->varchar('name');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('users');
    }
}
