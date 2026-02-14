<?php

declare(strict_types=1);

namespace Tempest\Database\Stubs;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class ObjectAlterMigrationStub implements MigratesUp, MigratesDown
{
    public string $name = 'dummy-date_dummy-migration-name';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('dummy-table-name');
    }

    public function down(): QueryStatement
    {
        return new AlterTableStatement('dummy-table-name');
    }
}
