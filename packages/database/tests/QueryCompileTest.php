<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Query;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\GenericEventBus;
use Tempest\Mapper\SerializerFactory;

final class QueryCompileTest extends TestCase
{
    protected function tearDown(): void
    {
        GenericContainer::setInstance(instance: null);

        parent::tearDown();
    }

    private function createDatabaseWithMysqlDialect(): void
    {
        $config = new MysqlConfig();
        $connection = new PDOConnection(config: $config);

        $database = new GenericDatabase(
            connection: $connection,
            transactionManager: new GenericTransactionManager(connection: $connection),
            serializerFactory: new SerializerFactory(container: new GenericContainer()),
            eventBus: new GenericEventBus(
                container: new GenericContainer(),
                eventBusConfig: new EventBusConfig(),
            ),
        );

        $container = new GenericContainer();
        $container->singleton(
            className: Database::class,
            definition: $database,
        );
        GenericContainer::setInstance(instance: $container);
    }

    private function createDatabaseWithPostgresDialect(): void
    {
        $config = new PostgresConfig();
        $connection = new PDOConnection(config: $config);

        $database = new GenericDatabase(
            connection: $connection,
            transactionManager: new GenericTransactionManager(connection: $connection),
            serializerFactory: new SerializerFactory(container: new GenericContainer()),
            eventBus: new GenericEventBus(
                container: new GenericContainer(),
                eventBusConfig: new EventBusConfig(),
            ),
        );

        $container = new GenericContainer();
        $container->singleton(
            className: Database::class,
            definition: $database,
        );
        GenericContainer::setInstance(instance: $container);
    }

    private function createDatabaseWithSqliteDialect(): void
    {
        $config = new SQLiteConfig(path: ':memory:');
        $connection = new PDOConnection(config: $config);

        $database = new GenericDatabase(
            connection: $connection,
            transactionManager: new GenericTransactionManager(connection: $connection),
            serializerFactory: new SerializerFactory(container: new GenericContainer()),
            eventBus: new GenericEventBus(
                container: new GenericContainer(),
                eventBusConfig: new EventBusConfig(),
            ),
        );

        $container = new GenericContainer();
        $container->singleton(
            className: Database::class,
            definition: $database,
        );
        GenericContainer::setInstance(instance: $container);
    }

    #[Test]
    public function postgresql_converts_backticks_to_double_quotes(): void
    {
        $this->createDatabaseWithPostgresDialect();

        $query = new Query(sql: 'SELECT `name`, `email` FROM `users` WHERE `id` = ?');

        $this->assertSame(
            'SELECT "name", "email" FROM "users" WHERE "id" = ?',
            $query->compile()->toString(),
        );
    }

    #[Test]
    public function postgresql_handles_qualified_identifiers(): void
    {
        $this->createDatabaseWithPostgresDialect();

        $query = new Query(sql: 'SELECT `users`.`name` FROM `users`');

        $this->assertSame(
            'SELECT "users"."name" FROM "users"',
            $query->compile()->toString(),
        );
    }

    #[Test]
    public function postgresql_handles_reserved_words(): void
    {
        $this->createDatabaseWithPostgresDialect();

        $query = new Query(sql: 'SELECT `order`, `user`, `type` FROM `group`');

        $this->assertSame(
            'SELECT "order", "user", "type" FROM "group"',
            $query->compile()->toString(),
        );
    }

    #[Test]
    public function postgresql_passes_through_sql_without_backticks(): void
    {
        $this->createDatabaseWithPostgresDialect();

        $query = new Query(sql: 'SELECT 1');

        $this->assertSame(
            'SELECT 1',
            $query->compile()->toString(),
        );
    }

    #[Test]
    public function mysql_retains_backticks(): void
    {
        $this->createDatabaseWithMysqlDialect();

        $query = new Query(sql: 'SELECT `name` FROM `users` WHERE `id` = ?');

        $this->assertSame(
            'SELECT `name` FROM `users` WHERE `id` = ?',
            $query->compile()->toString(),
        );
    }

    #[Test]
    public function sqlite_retains_backticks(): void
    {
        $this->createDatabaseWithSqliteDialect();

        $query = new Query(sql: 'SELECT `name` FROM `users` WHERE `id` = ?');

        $this->assertSame(
            'SELECT `name` FROM `users` WHERE `id` = ?',
            $query->compile()->toString(),
        );
    }
}
