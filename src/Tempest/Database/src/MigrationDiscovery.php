<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Core\DiscoversPath;
use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Database\Migrations\Migration as MigrationModel;
use Tempest\Support\Reflection\ClassReflector;

final class MigrationDiscovery implements Discovery, DiscoversPath
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

        if ($class->is(GenericMigration::class)) {
            return;
        }

        $this->databaseConfig->addMigration($class->getName());
    }

    public function discoverPath(string $path): void
    {
        if (! str_ends_with($path, '.sql')) {
            return;
        }

        $fileName = pathinfo($path, PATHINFO_FILENAME);

        $contents = explode(';', file_get_contents($path));

        foreach ($contents as $i => $content) {
            if (! $content) {
                continue;
            }

            $migration = new GenericMigration(
                fileName: "{$fileName}_{$i}",
                content: $content,
            );

            $this->databaseConfig->addMigration($migration);
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->databaseConfig->getMigrations());
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $migrations = unserialize($payload, ['allowed_classes' => [MigrationModel::class, GenericMigration::class]]);

        $this->databaseConfig->setMigrations($migrations);
    }
}
