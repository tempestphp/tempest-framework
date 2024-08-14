<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

readonly class FooMigration implements Migration
{
    public function getName(): string
    {
        return 'foo';
    }

    public function up(): CreateTableStatement|null
    {
        return new CreateTableStatement(
            tableName: 'Foo',
            statements: [
                new PrimaryKeyStatement(),
                new TextStatement('bar'),
            ],
        );
    }

    public function down(): DropTableStatement|null
    {
        return null;
    }
}
