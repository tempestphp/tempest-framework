<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;

final readonly class CreateBookTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_book_table';
    }

    public function up(): QueryStatement|null
    {
        return (new CreateTableStatement(Book::table()->tableName))
            ->primary()
            ->text('title')
            ->belongsTo('Book.author_id', 'Author.id', nullable: true);
    }

    public function down(): QueryStatement|null
    {
        return new DropTableStatement(Book::table()->tableName);
    }
}
