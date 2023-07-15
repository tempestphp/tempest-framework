<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Database\DatabaseConfig;
use Tempest\Interfaces\Discoverer;
use Tempest\Interfaces\Migration;

final readonly class MigrationDiscoverer implements Discoverer
{
    public function __construct(private DatabaseConfig $databaseConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->implementsInterface(Migration::class)) {
            return;
        }

        $this->databaseConfig->addMigration($class->getName());
    }
}
