<?php

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Mapper\Context;

final class RawSqlDatabaseContext implements Context
{
    private(set) string $name = self::class;

    public function __construct(
        private(set) DatabaseDialect $dialect,
    ) {}
}
