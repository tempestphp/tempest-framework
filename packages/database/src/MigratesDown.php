<?php

declare(strict_types=1);

namespace Tempest\Database;

interface MigratesDown
{
    public string $name {
        get;
    }

    public function down(): QueryStatement;
}
