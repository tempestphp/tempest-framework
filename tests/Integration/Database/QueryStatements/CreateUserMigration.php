<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreateUserMigration implements Migration
{
    public function getName(): string
    {
        return '0000-01-01_create_users_table';
    }

    public function up(): QueryStatement|null
    {
        return (new CreateTableStatement('User'))
            ->varchar('name');
    }

    public function down(): QueryStatement|null
    {
        return (new DropTableStatement('User'));
    }
}
