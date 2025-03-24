<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

final class TableDefinition
{
    public function __construct(
        public string $name,
        public string $type = 'table',
    ) {}
}
