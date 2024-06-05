<?php

declare(strict_types=1);

namespace App\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\Query;

final readonly class CreateUserTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_user_table';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE User (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `email` VARCHAR(250) NOT NULL,
            `password` VARCHAR(250) NOT NULL
        )");
    }

    public function down(): Query|null
    {
        return new Query("DROP TABLE User");
    }
}
