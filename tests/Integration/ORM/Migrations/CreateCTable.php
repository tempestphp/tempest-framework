<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\Query;

final readonly class CreateCTable implements Migration
{
    public function getName(): string
    {
        return '100-create-c';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE C (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL
        )");
    }

    public function down(): Query|null
    {
        return new Query("DROP TABLE C");
    }
}
