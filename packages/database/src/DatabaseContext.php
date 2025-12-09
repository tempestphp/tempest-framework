<?php

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Mapper\Context;

final class DatabaseContext implements Context
{
    private(set) string $key = self::class;

    public function __construct(
        private(set) DatabaseDialect $dialect,
    ) {}
}
