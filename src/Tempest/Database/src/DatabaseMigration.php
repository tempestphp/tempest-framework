<?php

declare(strict_types=1);

namespace Tempest\Database;

interface DatabaseMigration
{
    public function getName(): string;

    public function up(): QueryStatement|null;

    public function down(): QueryStatement|null;
}
