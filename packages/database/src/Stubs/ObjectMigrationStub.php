<?php

declare(strict_types=1);

namespace Tempest\Database\Stubs;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class ObjectMigrationStub implements MigratesUp, MigratesDown
{
    public string $name = 'dummy-date_dummy-table-name';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('dummy-table-name')
            ->primary()
            ->datetime('created_at')
            ->datetime('updated_at');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('dummy-table-name');
    }
}
