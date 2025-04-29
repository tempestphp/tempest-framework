<?php

declare(strict_types=1);

namespace Tempest\Database;

interface DatabaseMigration
{
    public string $name {
        get;
    }

    public function up(): ?QueryStatement;

    public function down(): ?QueryStatement;
}
