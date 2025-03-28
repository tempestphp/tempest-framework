<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

final class TableMigrationDefinition
{
    public function __construct(
        public string $name,
        public string $type = 'table',
    ) {}
}
