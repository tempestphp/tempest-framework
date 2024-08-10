<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;

final readonly class CreateBookTable implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return '0000-00-00_create_book_table';
    }

    public function up(): Query|null
    {
        return $this->driver
            ->createQueryStatement('Book')
            ->createTable()
            ->primary()
            ->createColumn('title', 'TEXT')
            ->createColumn('author_id', 'INTEGER', nullable: true)
            ->createForeignKey('author_id', 'Author')
            ->toQuery();
    }

    public function down(): Query|null
    {
        return $this->driver
            ->createQueryStatement('Book')
            ->dropForeignKeyFor('Author')
            ->dropTable()
            ->toQuery();
    }
}
