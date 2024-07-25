<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\Query;

final readonly class CreateBTable implements Migration
{
    public function getName(): string
    {
        return '100-create-b';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE B (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `c_id` INTEGER
        )");
    }

    public function down(): Query|null
    {
        return new Query("DROP TABLE B");
    }
}
