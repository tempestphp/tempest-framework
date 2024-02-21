<?php

declare(strict_types=1);

namespace App\Migrations;

use Tempest\Database\Query;
use Tempest\Interface\Migration;

final readonly class CreateAuthorTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_author_table';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE Author (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL
        )");
    }

    public function down(): Query|null
    {
        return null;
    }
}
