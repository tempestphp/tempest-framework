<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Models\UserWithEager;

final class CreateUserWithEagerTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-05_create_users_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(UserWithEager::class)
            ->primary()
            ->text('name');
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(UserWithEager::class);
    }
}
