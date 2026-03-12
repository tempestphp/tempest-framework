<?php

declare(strict_types=1);

namespace Tempest\Database;

interface MigratesUp
{
    public string $name {
        get;
    }

    public function up(): QueryStatement;
}
