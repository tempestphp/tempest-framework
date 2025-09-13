<?php

namespace Tempest\Upgrade\Tests\Tempest2\Fixtures;

use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class MigrateUpMigration implements \Tempest\Database\MigratesUp
{
    public string $name {
        get => '00-00-0000';
    }

    public function up(): QueryStatement
    {
        return new CreateTableStatement('table')
            ->primary()
            ->datetime('createdAt');
    }
}
