<?php

declare(strict_types=1);

namespace Tempest\Database;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Database\Migrations\Migration as MigrationModel;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;

final class MigrationDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(private DatabaseConfig $databaseConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if (! $class->implementsInterface(Migration::class)) {
            return;
        }

        $this->databaseConfig->addMigration($class->getName());
    }

    public function createCachePayload(): string
    {
        return serialize($this->databaseConfig->migrations);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $migrations = unserialize($payload, ['allowed_classes' => [MigrationModel::class]]);

        $this->databaseConfig->migrations = $migrations;
    }
}
