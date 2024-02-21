<?php

declare(strict_types=1);

namespace App\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\Query;

final readonly class CreateBookTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_book_table';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE Book (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `title` TEXT NOT NULL,
            `author_id` INTEGER
        )");
    }

    public function down(): Query|null
    {
        return null;
    }
}
