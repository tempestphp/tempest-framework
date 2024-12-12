<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;

final class CreateAuthorTable implements DatabaseMigration
{
    private(set) public string $name = '0000-00-00_create_authors_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            'authors',
            [
                new PrimaryKeyStatement(),
                new TextStatement('name'),
                new TextStatement('type', nullable: true),
            ],
        );
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(Author::class);
    }
}
