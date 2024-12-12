<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

final class ObjectFactoryADatabaseMigration implements DatabaseMigration
{
    private(set) public string $name = 'object-a';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            'ObjectFactoryA',
            [
                new PrimaryKeyStatement(),
                new TextStatement('prop'),
            ],
        );
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
