<?php

declare(strict_types=1);

namespace Tempest\Database\Stubs;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Discovery\DoNotDiscover;

#[DoNotDiscover]
final class MigrationStub implements DatabaseMigration
{
    public string $name = 'dummy-date_dummy-table-name';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            tableName: 'dummy-table-name',
        )
            ->primary()
            ->text('name')
            ->datetime('created_at')
            ->datetime('updated_at');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement(
            tableName: 'dummy-table-name',
        );
    }
}
