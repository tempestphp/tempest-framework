<?php

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Database;
use Tempest\Database\DatabaseInitializer;
use Tempest\Database\GenericDatabase;
use Tempest\Framework\Testing\TestingDatabaseInitializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class DatabaseInitializerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container
            ->removeInitializer(TestingDatabaseInitializer::class)
            ->addInitializer(DatabaseInitializer::class);
    }

    #[Test]
    public function test_it_resolves_multiple_persistent_connections_by_tag(): void
    {
        $this->configureSqliteDatabase('main', 'multi-main.sqlite');
        $this->configureSqliteDatabase('backup', 'multi-backup.sqlite');

        $main = $this->container->get(Database::class, 'main');
        $backup = $this->container->get(Database::class, 'backup');

        $this->assertInstanceOf(GenericDatabase::class, $main);
        $this->assertInstanceOf(GenericDatabase::class, $backup);

        $this->assertNotSame($main->connection, $backup->connection);
        $this->assertSame($this->databasePath('multi-main.sqlite'), $main->connection->config->path);
        $this->assertSame($this->databasePath('multi-backup.sqlite'), $backup->connection->config->path);

        $this->assertSame($main->connection, $this->container->get(Connection::class, 'main'));
        $this->assertSame($backup->connection, $this->container->get(Connection::class, 'backup'));
    }

    #[Test]
    public function test_it_reuses_a_persistent_connection_for_the_same_connection_config(): void
    {
        $this->configureSqliteDatabase('main', 'persistent-main.sqlite');

        $first = $this->container->get(Database::class, 'main');

        $this->container->unregister(Database::class, tagged: true);
        $this->container->unregister(Connection::class, tagged: true);

        $second = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $first);
        $this->assertInstanceOf(GenericDatabase::class, $second);

        $this->assertSame($first->connection, $second->connection);
        $this->assertSame($second->connection, $this->container->get(Connection::class, 'main'));
    }

    #[Test]
    public function test_it_does_not_reuse_a_non_persistent_connection_for_the_same_connection_config(): void
    {
        $this->configureSqliteDatabase('main', 'non-persistent-main.sqlite', persistent: false);

        $first = $this->container->get(Database::class, 'main');

        $this->container->unregister(Database::class, tagged: true);
        $this->container->unregister(Connection::class, tagged: true);

        $second = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $first);
        $this->assertInstanceOf(GenericDatabase::class, $second);

        $this->assertNotSame($first->connection, $second->connection);
        $this->assertSame($this->databasePath('non-persistent-main.sqlite'), $first->connection->config->path);
        $this->assertSame($this->databasePath('non-persistent-main.sqlite'), $second->connection->config->path);
        $this->assertSame($second->connection, $this->container->get(Connection::class, 'main'));
    }

    #[Test]
    public function test_it_does_not_reuse_a_persistent_connection_for_the_same_tag_with_a_different_connection_config(): void
    {
        $this->configureSqliteDatabase('main', 'first-main.sqlite');

        $first = $this->container->get(Database::class, 'main');

        $this->container->unregister(Database::class, tagged: true);
        $this->container->unregister(Connection::class, tagged: true);

        $this->configureSqliteDatabase('main', 'second-main.sqlite');

        $second = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $first);
        $this->assertInstanceOf(GenericDatabase::class, $second);

        $this->assertNotSame($first->connection, $second->connection);
        $this->assertSame($this->databasePath('first-main.sqlite'), $first->connection->config->path);
        $this->assertSame($this->databasePath('second-main.sqlite'), $second->connection->config->path);
        $this->assertSame($second->connection, $this->container->get(Connection::class, 'main'));
    }

    private function configureSqliteDatabase(string $tag, string $filename, bool $persistent = true): void
    {
        $path = $this->databasePath($filename);

        if (is_file($path)) {
            unlink($path);
        }

        $this->container->config(new SQLiteConfig(
            path: $path,
            persistent: $persistent,
            tag: $tag,
        ));
    }

    private function databasePath(string $filename): string
    {
        return $this->internalStorage . '/' . $filename;
    }
}
