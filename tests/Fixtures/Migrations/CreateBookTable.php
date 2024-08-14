<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\BelongsToStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tempest\Database\QueryStatements\TextStatement;

final readonly class CreateBookTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_book_table';
    }

    public function up(): CreateTableStatement|null
    {
        return new CreateTableStatement(
            'Book',
            [
                new PrimaryKeyStatement(),
                new TextStatement('title'),
                new RawStatement('author_id INTEGER'),
                new BelongsToStatement('Book.author_id', 'Author.id'),
            ]
        );
    }

    public function down(): DropTableStatement|null
    {
        return new DropTableStatement('Book');
    }
}
