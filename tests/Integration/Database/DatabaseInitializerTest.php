<?php

namespace Tests\Tempest\Integration\Database;

use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use RuntimeException;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Connection\ConnectionInitializer;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\DatabaseInitializer;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Query;
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
    public function it_resolves_multiple_persistent_connections_by_tag(): void
    {
        $this->configureSqliteDatabase('main', 'multi-main.sqlite');
        $this->configureSqliteDatabase('backup', 'multi-backup.sqlite');

        $main = $this->container->get(Database::class, 'main');
        $backup = $this->container->get(Database::class, 'backup');

        $this->assertInstanceOf(GenericDatabase::class, $main);
        $this->assertInstanceOf(GenericDatabase::class, $backup);

        $this->assertNotSame($main->connection, $backup->connection);
        // @phpstan-ignore-next-line
        $this->assertSame($this->databasePath('multi-main.sqlite'), $main->connection->config->path);
        // @phpstan-ignore-next-line
        $this->assertSame($this->databasePath('multi-backup.sqlite'), $backup->connection->config->path);

        $this->assertSame($main->connection, $this->container->get(Connection::class, 'main'));
        $this->assertSame($backup->connection, $this->container->get(Connection::class, 'backup'));
    }

    #[Test]
    public function it_resolves_tagged_connections_before_databases_without_sharing_them(): void
    {
        $this->configureSqliteDatabase('main', 'connection-first-main.sqlite');
        $this->configureSqliteDatabase('backup', 'connection-first-backup.sqlite');

        $mainConnection = $this->container->get(Connection::class, 'main');
        $backupConnection = $this->container->get(Connection::class, 'backup');

        $this->assertInstanceOf(PDOConnection::class, $mainConnection);
        $this->assertInstanceOf(PDOConnection::class, $backupConnection);
        $this->assertNotSame($mainConnection, $backupConnection);
        $main = $this->container->get(Database::class, 'main');
        $backup = $this->container->get(Database::class, 'backup');
        $this->assertInstanceOf(GenericDatabase::class, $main);
        $this->assertInstanceOf(GenericDatabase::class, $backup);
        $this->assertSame($mainConnection, $main->connection);
        $this->assertSame($backupConnection, $backup->connection);
        $this->assertSame('main', $main->tag);
        $this->assertSame('backup', $backup->tag);
    }

    #[Test]
    public function it_honours_an_injected_connection_when_resolving_database_first(): void
    {
        $this->configureSqliteDatabase('main', 'injected-database-first.sqlite');
        $connection = $this->createMock(Connection::class);
        $this->container->singleton(Connection::class, $connection, 'main');

        $database = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $database);
        $this->assertSame($connection, $database->connection);
        $this->assertSame($connection, $this->container->get(Connection::class, 'main'));
        $this->assertSame(DatabaseDialect::SQLITE, $database->dialect);
        $this->assertSame('main', $database->tag);
    }

    #[Test]
    public function it_honours_an_untagged_injected_connection(): void
    {
        $connection = $this->createMock(Connection::class);
        $this->container->unregister(Database::class);
        $this->container->singleton(Connection::class, $connection);

        $database = $this->container->get(Database::class);

        $this->assertInstanceOf(GenericDatabase::class, $database);
        $this->assertSame($connection, $database->connection);
        $this->assertSame($connection, $this->container->get(Connection::class));
        $this->assertSame($this->container->get(DatabaseConfig::class)->dialect, $database->dialect);
        $this->assertSame($this->container->get(DatabaseConfig::class)->tag, $database->tag);
    }

    #[Test]
    public function it_keeps_injected_tagged_connections_separate(): void
    {
        $this->configureSqliteDatabase('main', 'injected-main.sqlite');
        $this->configureSqliteDatabase('backup', 'injected-backup.sqlite');

        $mainConnection = $this->createMock(Connection::class);
        $backupConnection = $this->createMock(Connection::class);
        $this->container->singleton(Connection::class, $mainConnection, 'main');
        $this->container->singleton(Connection::class, $backupConnection, 'backup');

        $main = $this->container->get(Database::class, 'main');
        $backup = $this->container->get(Database::class, 'backup');
        $this->assertInstanceOf(GenericDatabase::class, $main);
        $this->assertInstanceOf(GenericDatabase::class, $backup);
        $this->assertSame($mainConnection, $main->connection);
        $this->assertSame($backupConnection, $backup->connection);
        $this->assertSame('main', $main->tag);
        $this->assertSame('backup', $backup->tag);
    }

    #[Test]
    public function it_honours_an_injected_connection_when_resolving_connection_first(): void
    {
        $this->configureSqliteDatabase('main', 'injected-connection-first.sqlite');
        $connection = $this->createMock(Connection::class);
        $this->container->singleton(Connection::class, $connection, 'main');

        $this->assertSame($connection, $this->container->get(Connection::class, 'main'));

        $database = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $database);
        $this->assertSame($connection, $database->connection);
    }

    #[Test]
    public function it_uses_the_injected_connection_in_an_externally_managed_transaction(): void
    {
        $this->configureSqliteDatabase('main', 'external-transaction.sqlite');

        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE outbox (message TEXT)');

        $connection = $this->createMock(Connection::class);
        $connection
            ->method('prepare')
            ->willReturnCallback(
                fn (string $sql): PDOStatement => $pdo->prepare($sql) ?: throw new RuntimeException('Could not prepare SQL statement.'),
            );
        $connection->expects($this->never())->method('connect');
        $connection->expects($this->never())->method('reconnect');
        $connection->expects($this->never())->method('ping');

        $this->container->singleton(Connection::class, $connection, 'main');
        $database = $this->container->get(Database::class, 'main');

        $pdo->beginTransaction();
        $database->execute(new Query("INSERT INTO outbox (message) VALUES ('application')"));
        $pdo->exec("INSERT INTO outbox (message) VALUES ('messenger')");

        $this->assertCount(2, $database->fetch(new Query('SELECT * FROM outbox')));

        $pdo->rollBack();

        $this->assertSame([], $database->fetch(new Query('SELECT * FROM outbox')));
    }

    #[Test]
    public function the_testing_initializer_also_honours_an_injected_connection(): void
    {
        $this->container
            ->removeInitializer(DatabaseInitializer::class)
            ->addInitializer(TestingDatabaseInitializer::class);

        $this->configureSqliteDatabase('main', 'injected-testing.sqlite');
        $connection = $this->createMock(Connection::class);
        $this->container->singleton(Connection::class, $connection, 'main');

        $database = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $database);
        $this->assertSame($connection, $database->connection);
        $this->assertSame('main', $database->tag);
    }

    #[Test]
    public function it_reuses_a_persistent_connection_for_the_same_connection_config(): void
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
    public function it_does_not_reuse_a_non_persistent_connection_for_the_same_connection_config(): void
    {
        $this->configureSqliteDatabase('main', 'non-persistent-main.sqlite', persistent: false);

        $first = $this->container->get(Database::class, 'main');

        $this->container->unregister(Database::class, tagged: true);
        $this->container->unregister(Connection::class, tagged: true);

        $second = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $first);
        $this->assertInstanceOf(GenericDatabase::class, $second);

        $this->assertNotSame($first->connection, $second->connection);
        // @phpstan-ignore-next-line
        $this->assertSame($this->databasePath('non-persistent-main.sqlite'), $first->connection->config->path);
        // @phpstan-ignore-next-line
        $this->assertSame($this->databasePath('non-persistent-main.sqlite'), $second->connection->config->path);
        $this->assertSame($second->connection, $this->container->get(Connection::class, 'main'));
    }

    #[Test]
    public function it_does_not_reuse_a_persistent_connection_for_the_same_tag_with_a_different_connection_config(): void
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
        // @phpstan-ignore-next-line
        $this->assertSame($this->databasePath('first-main.sqlite'), $first->connection->config->path);
        // @phpstan-ignore-next-line
        $this->assertSame($this->databasePath('second-main.sqlite'), $second->connection->config->path);
        $this->assertSame($second->connection, $this->container->get(Connection::class, 'main'));
    }

    #[Test]
    public function it_does_not_reuse_a_persistent_connection_when_only_the_password_differs(): void
    {
        $initializer = new ConnectionInitializer();
        $method = new ReflectionMethod($initializer, 'getConnectionKey');

        $first = new MysqlConfig(
            host: 'localhost',
            username: 'tempest',
            password: 'first-password', // @mago-expect lint:no-literal-password
            database: 'tempest',
            persistent: true,
            tag: 'main',
        );

        $second = new MysqlConfig(
            host: 'localhost',
            username: 'tempest',
            password: 'second-password', // @mago-expect lint:no-literal-password
            database: 'tempest',
            persistent: true,
            tag: 'main',
        );

        $this->assertNotSame(
            $method->invoke($initializer, $first),
            $method->invoke($initializer, $second),
        );
    }

    #[Test]
    public function it_reconnects_a_stale_persistent_connection_when_reusing_it(): void
    {
        $this->configureSqliteDatabase('main', 'stale-persistent-main.sqlite');

        $first = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $first);
        $this->assertInstanceOf(PDOConnection::class, $first->connection);

        $first->connection->close();

        $this->container->unregister(Database::class, tagged: true);
        $this->container->unregister(Connection::class, tagged: true);

        $second = $this->container->get(Database::class, 'main');

        $this->assertInstanceOf(GenericDatabase::class, $second);
        $this->assertSame($first->connection, $second->connection);
        $this->assertTrue($second->connection->ping());
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
