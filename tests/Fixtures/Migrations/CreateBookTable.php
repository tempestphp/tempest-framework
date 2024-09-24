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
        return '0000-00-00_create_books_table';
    }

    public function up(): QueryStatement|null
    {
        return CreateTableStatement::forModel(Book::class)
            ->primary()
            ->text('title')
            ->belongsTo('books.author_id', 'authors.id', nullable: true);
    }

    public function down(): QueryStatement|null
    {
        return DropTableStatement::forModel(Book::class);
    }
}
