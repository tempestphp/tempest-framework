<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Database\Migrations\Migration as MigrationModel;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\ClassReflector;

final class MigrationDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(private DatabaseConfig $databaseConfig)
    {
    }

    public function discover(ClassReflector $class): void
    {
        if (! $class->implements(Migration::class)) {
            return;
        }

        $this->databaseConfig->addMigration($class->getName());
    }

    public function createCachePayload(): string
    {
        return serialize($this->databaseConfig->getMigrations());
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $migrations = unserialize($payload, ['allowed_classes' => [MigrationModel::class]]);

        $this->databaseConfig->setMigrations($migrations);
    }
}
