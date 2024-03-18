<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

final readonly class MigrationRolledBack
{
    public function __construct(public string $name)
    {
    }
}
