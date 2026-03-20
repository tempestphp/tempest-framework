<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreateBookTagTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-11_create_books_tags_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('books_tags')
            ->primary()
            ->belongsTo('books_tags.book_id', 'books.id')
            ->belongsTo('books_tags.tag_id', 'tags.id');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('books_tags');
    }
}
