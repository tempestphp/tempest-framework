<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Core\DoNotDiscover;
use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

#[DoNotDiscover]
final readonly class PermissionMigration implements Migration
{
    public function getName(): string
    {
        return '0000-00-01_create_permissions_table';
    }

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
