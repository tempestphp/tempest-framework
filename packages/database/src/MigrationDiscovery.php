<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Database\Migrations\RunnableMigrations;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Filesystem;

final class MigrationDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(MigratesUp::class) && ! $class->implements(MigratesDown::class)) {
            return;
        }

        if ($class->is(GenericDatabaseMigration::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        if (! str_ends_with($path, '.sql')) {
            return;
        }

        if (str_ends_with($path, '.stub.sql')) {
            return;
        }

        $fileName = pathinfo($path, PATHINFO_FILENAME);

        $contents = explode(';', Filesystem\read_file($path));

        foreach ($contents as $i => $content) {
            if (! $content) {
                continue;
            }

            $migration = new GenericDatabaseMigration(
                fileName: "{$fileName}_{$i}",
                content: $content,
            );

            $this->discoveryItems->add($location, $migration);
        }
    }

    public function apply(): void
    {
        /** @var array<MigratesUp|MigratesDown> $resolved */
        $resolved = [];

        foreach ($this->discoveryItems as $discoveryItem) {
            if (is_string($discoveryItem)) {
                $resolved[] = $this->container->get($discoveryItem);
            } elseif ($discoveryItem instanceof MigratesUp || $discoveryItem instanceof MigratesDown) {
                $resolved[] = $discoveryItem;
            }
        }

        $this->container->singleton(RunnableMigrations::class, new RunnableMigrations($resolved));
    }
}
