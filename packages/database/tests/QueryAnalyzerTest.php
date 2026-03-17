<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryAnalyzer;
use Tempest\Database\QueryExecuted;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\GenericEventBus;
use Tempest\EventBus\Testing\FakeEventBus;
use Tempest\Mapper\SerializerFactory;

/**
 * @internal
 */
final class QueryAnalyzerTest extends TestCase
{
    private FakeEventBus $eventBus;

    private GenericDatabase $database;

    protected function setUp(): void
    {
        parent::setUp();

        $eventBusConfig = new EventBusConfig();

        $this->eventBus = new FakeEventBus(
            eventBus: new GenericEventBus(
                container: new GenericContainer(),
                eventBusConfig: $eventBusConfig,
            ),
            eventBusConfig: $eventBusConfig,
        );

        $config = new SQLiteConfig(path: ':memory:');
        $connection = new PDOConnection($config);
        $connection->connect();

        $this->database = new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
            new SerializerFactory(new GenericContainer()),
            $this->eventBus,
        );

        $container = new GenericContainer();
        $container->singleton(Database::class, $this->database);
        GenericContainer::setInstance($container);
    }

    protected function tearDown(): void
    {
        GenericContainer::setInstance(null);

        parent::tearDown();
    }

    private function lastEvent(): QueryExecuted
    {
        return array_last($this->eventBus->dispatched);
    }

    #[Test]
    public function explain_returns_null_for_non_select_queries(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER)'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertNull($analyzer->explain());
    }

    #[Test]
    public function explain_returns_result_for_select_queries(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $this->database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $this->database->fetch(new Query('SELECT * FROM test WHERE id = 1'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertTrue($analyzer->query->isSelect());

        $explain = $analyzer->explain();

        $this->assertIsArray($explain);
        $this->assertNotEmpty($explain);
    }

    #[Test]
    public function explain_caches_result(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER)'));
        $this->database->fetch(new Query('SELECT * FROM test'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $firstCall = $analyzer->explain();
        $secondCall = $analyzer->explain();

        $this->assertSame($firstCall, $secondCall);
    }

    #[Test]
    public function uses_full_table_scan_detection(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $this->database->fetch(new Query('SELECT * FROM test'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertTrue($analyzer->usesFullTableScan());
    }

    #[Test]
    public function uses_full_table_scan_returns_false_for_non_select(): void
    {
        $event = new QueryExecuted('INSERT INTO test VALUES (1)', [], 0.0, null, false);
        $analyzer = new QueryAnalyzer($event, $this->database);

        $this->assertFalse($analyzer->usesFullTableScan());
    }

    #[Test]
    public function get_rows_examined(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $this->database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $this->database->execute(new Query('INSERT INTO test (id, name) VALUES (2, "test2")'));
        $this->database->fetch(new Query('SELECT * FROM test'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertGreaterThanOrEqual(0, $analyzer->getRowsExamined());
    }

    #[Test]
    public function get_rows_examined_returns_zero_for_non_select(): void
    {
        $event = new QueryExecuted('INSERT INTO test VALUES (1)', [], 0.0, null, false);
        $analyzer = new QueryAnalyzer($event, $this->database);

        $this->assertSame(0, $analyzer->getRowsExamined());
    }

    #[Test]
    public function uses_index_detection(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)'));
        $this->database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $this->database->fetch(new Query('SELECT * FROM test WHERE id = 1'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertTrue($analyzer->usesIndex());
    }

    #[Test]
    public function uses_index_returns_false_for_full_table_scan(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $this->database->fetch(new Query('SELECT * FROM test'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertFalse($analyzer->usesIndex());
    }

    #[Test]
    public function get_index_used(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)'));
        $this->database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $this->database->fetch(new Query('SELECT * FROM test WHERE id = 1'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertIsString($analyzer->getIndexUsed());
        $this->assertNotEmpty($analyzer->getIndexUsed());
    }

    #[Test]
    public function get_index_used_returns_null_when_no_index(): void
    {
        $this->database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $this->database->fetch(new Query('SELECT * FROM test'));

        $analyzer = new QueryAnalyzer($this->lastEvent(), $this->database);

        $this->assertNull($analyzer->getIndexUsed());
    }
}
