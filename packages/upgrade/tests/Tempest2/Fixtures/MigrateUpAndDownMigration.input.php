<?php

namespace Tempest\Upgrade\Tests\Tempest2\Fixtures;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class MigrateUpAndDownMigration implements DatabaseMigration
{
    public string $name {
        get => '00-00-0000';
    }

    public function up(): ?QueryStatement
    {
        return new CreateTableStatement('table')
            ->primary()
            ->datetime('createdAt');
    }

    public function down(): ?QueryStatement
    {
        return new DropTableStatement('table');
    }
}
