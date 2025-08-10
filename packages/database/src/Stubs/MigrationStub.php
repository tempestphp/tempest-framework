<?php

declare(strict_types=1);

namespace Tempest\Database\Stubs;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class MigrationStub implements MigratesUp
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
}
