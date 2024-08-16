<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final readonly class CreateBookTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_book_table';
    }

    public function up(): CreateTableStatement|null
    {
        return (new CreateTableStatement('Book'))
            ->primary()
            ->text('title')
            ->belongsTo('Book.author_id', 'Author.id', nullable: true);
    }

    public function down(): DropTableStatement|null
    {
        return new DropTableStatement('Book');
    }
}
