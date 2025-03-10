<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

final readonly class MigrationMigrated
{
    public function __construct(
        public string $name,
    ) {
    }
}
