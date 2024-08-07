<?php

declare(strict_types=1);

namespace Tempest\Database;

final class DatabaseConfig
{
    private array $migrations = [];

    public function __construct(
        public readonly DatabaseDriver $driver,
    ) {
    }

    public function driver(): DatabaseDriver
    {
        return $this->driver;
    }

    public function addMigration(string $className): self
    {
        $this->migrations[$className] = $className;

        return $this;
    }

    public function set(array $migrations): void
    {
        $this->migrations = $migrations;
    }

    public function get(string $sort = 'asc'): array
    {
        match ($sort) {
            'asc' => ksort($this->migrations),
            default => rsort($this->migrations)
        };

        return $this->migrations;
    }
}
