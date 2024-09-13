<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

final readonly class FooMigration implements Migration
{
    public function getName(): string
    {
        return 'foo';
    }

    public function up(): QueryStatement|null
    {
        return new CreateTableStatement(
            tableName: 'Foo',
            statements: [
                new PrimaryKeyStatement(),
                new TextStatement('bar'),
            ],
        );
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
