<?php

namespace Tempest\Upgrade\Tests\Tempest20\Fixtures;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class MigrateUpMigration implements DatabaseMigration
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
        return null;
    }
}
