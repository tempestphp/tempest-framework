<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\Query;

final readonly class CreateMigrationsTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_migrations_table';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE IF NOT EXISTS Migration (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL
        )");
    }

    public function down(): Query|null
    {
        return new Query('DROP TABLE IF EXISTS Migration');
    }
}
