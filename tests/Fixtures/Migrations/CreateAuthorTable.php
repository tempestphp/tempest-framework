<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;

final class CreateAuthorTable implements DatabaseMigration
{
    private(set) string $name = '0000-00-01_create_authors_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Author::class)
            ->primary()
            ->text('name')
            ->text('type', nullable: true)
            ->belongsTo('authors.publisher_id', 'publishers.id', nullable: true);
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(Author::class);
    }
}
