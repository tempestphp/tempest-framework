<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;

final readonly class CreateMigrationsTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_migrations_table';
    }

    public function up(): CreateTableStatement|null
    {
        return new CreateTableStatement(
            tableName: 'Migration',
            statements: [
                new PrimaryKeyStatement('id'),
                new RawStatement('name TEXT NOT NULL'),
            ],
        );
    }

    public function down(): DropTableStatement|null
    {
        return new DropTableStatement('Migration');
    }
}
