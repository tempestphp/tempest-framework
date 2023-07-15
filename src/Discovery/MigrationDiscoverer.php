<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Database\DatabaseConfig;
use Tempest\Interfaces\DatabaseMigration;
use Tempest\Interfaces\Discoverer;

final readonly class MigrationDiscoverer implements Discoverer
{
    public function __construct(private DatabaseConfig $databaseConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->implementsInterface(DatabaseMigration::class)) {
            return;
        }

        $this->databaseConfig->addMigration($class->getName());
    }
}
