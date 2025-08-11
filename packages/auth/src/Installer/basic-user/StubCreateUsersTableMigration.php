<?php

namespace Tempest\Auth\Installer;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class StubCreateUsersTableMigration implements MigratesUp
{
    public string $name = '0000-00-00_create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->primary()
            ->string('email')
            ->string('password', nullable: true);
    }
}
