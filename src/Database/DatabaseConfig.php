<?php

namespace Tempest\Database;

use Tempest\Interfaces\DatabaseDriver;

final class DatabaseConfig
{
    public function __construct(
        public readonly DatabaseDriver $driver,
        public array $migrations = [],
    ) {}

    public function addMigration(string $className): self
    {
        $this->migrations[] = $className;

        return $this;
    }
}