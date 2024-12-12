<?php

declare(strict_types=1);

namespace Tempest\Auth\Install;

use Tempest\Core\DoNotDiscover;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

#[DoNotDiscover]
final class PermissionDatabaseMigration implements DatabaseMigration
{
    private(set) string $name = '0000-00-01_create_permissions_table';

    public function up(): CreateTableStatement
    {
        return (new CreateTableStatement('permissions'))
            ->primary()
            ->varchar('name');
    }

    public function down(): DropTableStatement
    {
        return DropTableStatement::forModel(Permission::class);
    }
}
