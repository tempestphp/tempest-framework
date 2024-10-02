<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Core\CanBePublished;
use Tempest\Database\Migration;
use Tempest\Database\Migrations\Migration as Model;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

#[CanBePublished]
final readonly class CreateMigrationsTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_migrations_table';
    }

    public function up(): QueryStatement|null
    {
        return (new CreateTableStatement(Model::table()->tableName))
            ->primary()
            ->text('name');
    }

    public function down(): QueryStatement|null
    {
        return new DropTableStatement(Model::table()->tableName);
    }
}
