<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Interface\DatabaseDriver;

final class DatabaseConfig
{
    public function __construct(
        public readonly DatabaseDriver $driver,
        public array $migrations = [],
    ) {
    }

    public function addMigration(string $className): self
    {
        $this->migrations[] = $className;

        return $this;
    }
}
