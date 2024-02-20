<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Database\DatabaseConfig;
use Tempest\Interface\Container;
use Tempest\Interface\Discovery;
use Tempest\Interface\Migration;

final class MigrationDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/migration-discovery.cache.php';

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

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->databaseConfig->migrations));
    }

    public function restoreCache(Container $container): void
    {
        $migrations = unserialize(file_get_contents(self::CACHE_PATH));

        $this->databaseConfig->migrations = $migrations;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
