<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;

final class CreateIsbnTable implements DatabaseMigration
{
    private(set) string $name = '0000-00-00_create_isbns_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Isbn::class)
            ->primary()
            ->text('value')
            ->belongsTo('isbns.book_id', 'books.id');
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(Book::class);
    }
}
