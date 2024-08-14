<?php

declare(strict_types=1);

namespace Tempest\Database;

interface QueryStatement
{
    public function compile(DatabaseDialect $dialect): string;
}
