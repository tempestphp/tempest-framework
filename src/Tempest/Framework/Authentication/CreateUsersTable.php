<?php

namespace Tempest\Framework\Authentication;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateUsersTable implements MigratesUp
{
    public string $name = '0000-00-00_create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->primary()
            ->string('email')
            ->string('password', nullable: true)
            ->string('name', nullable: true)
            ->string('nickname', nullable: true)
            ->string('avatar', nullable: true)
            ->string('oauth_id', nullable: true)
            ->string('oauth_raw', nullable: true)
            ->string('oauth_provider', nullable: true);
    }
}
