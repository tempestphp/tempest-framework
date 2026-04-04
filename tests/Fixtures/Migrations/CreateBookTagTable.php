<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\OnDelete;

final class CreateBookTagTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-11_create_books_tags_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(tableName: 'books_tags')
            ->primary()
            ->belongsTo(local: 'books_tags.book_id', foreign: 'books.id', onDelete: OnDelete::CASCADE)
            ->belongsTo(local: 'books_tags.tag_id', foreign: 'tags.id', onDelete: OnDelete::CASCADE);
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement(tableName: 'books_tags');
    }
}
