<?php

namespace Tests\Tempest\Integration\Container;

use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Database;
use Tempest\Database\DatabaseInitializer;
use Tempest\Mapper\SerializerFactory;

final class TaggedDynamicInitializerTest extends TestCase
{
    public function test_resolve(): void
    {
        $container = new GenericContainer();
        $container->singleton(SerializerFactory::class, new SerializerFactory($container));
        $container->addInitializer(DatabaseInitializer::class);

        $container->config(new SQLiteConfig(
            path: 'db-main.sqlite',
        ));

        $container->config(new SQLiteConfig(
            path: 'db-tenant-1.sqlite',
            tag: 'tenant-1',
        ));

        $container->config(new SQLiteConfig(
            path: 'db-tenant-2.sqlite',
            tag: 'tenant-2',
        ));

        $tenant1 = $container->get(Database::class, tag: 'tenant-1');
        $tenant2 = $container->get(Database::class, tag: 'tenant-2');
        $main = $container->get(Database::class);

        /** @phpstan-ignore-next-line */
        $this->assertSame('db-tenant-1.sqlite', $tenant1->connection->config->path);
        /** @phpstan-ignore-next-line */
        $this->assertSame('db-tenant-2.sqlite', $tenant2->connection->config->path);
        /** @phpstan-ignore-next-line */
        $this->assertSame('db-main.sqlite', $main->connection->config->path);
    }
}
