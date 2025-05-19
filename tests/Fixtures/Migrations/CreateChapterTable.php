<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;

final class CreateChapterTable implements DatabaseMigration
{
    private(set) string $name = '0000-00-03_create_chapters_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Chapter::class)
            ->primary()
            ->text('title')
            ->text('contents', nullable: true, default: '')
            ->belongsTo('chapters.book_id', 'books.id');
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(Book::class);
    }
}
