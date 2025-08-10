<?php

namespace Tempest\Auth\Installer;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class StubCreateUsersTableMigration implements MigratesUp
{
    public string $name = '0000-create-users-table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->primary()
            ->string('email')
            ->string('password', nullable: true);
    }
}
