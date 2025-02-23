<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;

interface QueryStatement
{
    public function compile(DatabaseDialect $dialect): string;
}
