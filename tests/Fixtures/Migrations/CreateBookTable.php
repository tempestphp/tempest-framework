<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;

final class CreateBookTable implements DatabaseMigration
{
    private(set) public string $name = '0000-00-00_create_books_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Book::class)
            ->primary()
            ->text('title')
            ->belongsTo('books.author_id', 'authors.id', nullable: true);
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(Book::class);
    }
}
