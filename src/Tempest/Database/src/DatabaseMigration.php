<?php

declare(strict_types=1);

namespace Tempest\Database;

interface DatabaseMigration
{
    public string $name {
        get;
    }

    public function up(): QueryStatement|null;

    public function down(): QueryStatement|null;
}
