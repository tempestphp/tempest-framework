<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryEventDispatcher;
use Tempest\Database\QueryExecuted;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\GenericEventBus;
use Tempest\EventBus\Testing\FakeEventBus;
use Tempest\Mapper\SerializerFactory;

/**
 * @internal
 */
final class QueryExecutedTest extends TestCase
{
    protected function tearDown(): void
    {
        GenericContainer::setInstance(null);

        parent::tearDown();
    }

    private function createFakeEventBus(): FakeEventBus
    {
        return new FakeEventBus(
            genericEventBus: new GenericEventBus(
                container: new GenericContainer(),
                eventBusConfig: new EventBusConfig(),
            ),
        );
    }

    private function createDatabase(FakeEventBus $eventBus, ?string $tag = null): GenericDatabase
    {
        $config = new SQLiteConfig(path: ':memory:', tag: $tag);
        $connection = new PDOConnection($config);
        $connection->connect();

        $database = new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
            new SerializerFactory(new GenericContainer()),
            new QueryEventDispatcher($eventBus),
        );

        $container = new GenericContainer();
        $container->singleton(Database::class, $database);
        GenericContainer::setInstance($container);

        return $database;
    }

    #[Test]
    public function execute_dispatches_query_executed_event(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER)'));

        $this->assertCount(1, $eventBus->dispatched);
        $event = $eventBus->dispatched[0];
        $this->assertInstanceOf(QueryExecuted::class, $event);
        $this->assertSame('CREATE TABLE test (id INTEGER)', $event->sql);
        $this->assertSame([], $event->bindings);
        $this->assertFalse($event->failed);
        $this->assertGreaterThanOrEqual(0.0, $event->durationMs);
        $this->assertNull($event->connectionName);
    }

    #[Test]
    public function fetch_dispatches_query_executed_event(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER)'));
        $database->fetch(new Query('SELECT * FROM test'));

        $this->assertCount(2, $eventBus->dispatched);
        $event = $eventBus->dispatched[1];
        $this->assertInstanceOf(QueryExecuted::class, $event);
        $this->assertSame('SELECT * FROM test', $event->sql);
        $this->assertFalse($event->failed);
    }

    #[Test]
    public function execute_dispatches_event_on_failure(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        try {
            $database->execute(new Query('INVALID SQL'));
        } catch (QueryWasInvalid) { // @mago-expect lint:no-empty-catch-clause
        }

        $this->assertCount(1, $eventBus->dispatched);
        $event = $eventBus->dispatched[0];
        $this->assertInstanceOf(QueryExecuted::class, $event);
        $this->assertTrue($event->failed);
    }

    #[Test]
    public function fetch_dispatches_event_on_failure(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        try {
            $database->fetch(new Query('INVALID SQL'));
        } catch (QueryWasInvalid) { // @mago-expect lint:no-empty-catch-clause
        }

        $this->assertCount(1, $eventBus->dispatched);
        $event = $eventBus->dispatched[0];
        $this->assertInstanceOf(QueryExecuted::class, $event);
        $this->assertTrue($event->failed);
    }

    #[Test]
    public function query_executed_event_contains_connection_name(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus, tag: 'reporting');

        $database->execute(new Query('CREATE TABLE test (id INTEGER)'));

        $this->assertCount(1, $eventBus->dispatched);
        $event = $eventBus->dispatched[0];
        $this->assertInstanceOf(QueryExecuted::class, $event);
        $this->assertSame('reporting', $event->connectionName);
    }

    #[Test]
    public function explain_returns_null_for_non_select_queries(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER)'));

        $event = $eventBus->dispatched[0];
        $this->assertNull($event->explain());
    }

    #[Test]
    public function explain_returns_explain_result_for_select_queries(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $database->fetch(new Query('SELECT * FROM test WHERE id = 1'));

        $event = $eventBus->dispatched[2];

        $this->assertTrue($event->isSelect());

        $explain = $event->explain();

        $this->assertIsArray($explain);
        $this->assertNotEmpty($explain);
    }

    #[Test]
    public function explain_caches_result(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER)'));
        $database->fetch(new Query('SELECT * FROM test'));

        $event = $eventBus->dispatched[1];
        $firstCall = $event->explain();
        $secondCall = $event->explain();

        $this->assertSame($firstCall, $secondCall);
    }

    #[Test]
    public function is_slow_returns_true_for_slow_queries(): void
    {
        $event = new QueryExecuted(
            sql: 'SELECT * FROM test',
            bindings: [],
            durationMs: 150.0,
            connectionName: null,
            failed: false,
        );

        $this->assertTrue($event->isSlow());
        $this->assertTrue($event->isSlow(100.0));
        $this->assertFalse($event->isSlow(200.0));
    }

    #[Test]
    public function is_slow_returns_false_for_fast_queries(): void
    {
        $event = new QueryExecuted(
            sql: 'SELECT * FROM test',
            bindings: [],
            durationMs: 50.0,
            connectionName: null,
            failed: false,
        );

        $this->assertFalse($event->isSlow());
    }

    #[Test]
    public function query_type_detection(): void
    {
        $selectEvent = new QueryExecuted('SELECT * FROM test', [], 0.0, null, false);
        $insertEvent = new QueryExecuted('INSERT INTO test VALUES (1)', [], 0.0, null, false);
        $updateEvent = new QueryExecuted('UPDATE test SET x = 1', [], 0.0, null, false);
        $deleteEvent = new QueryExecuted('DELETE FROM test', [], 0.0, null, false);
        $createEvent = new QueryExecuted('CREATE TABLE test (id INT)', [], 0.0, null, false);

        $this->assertTrue($selectEvent->isSelect());
        $this->assertFalse($selectEvent->isInsert());
        $this->assertFalse($selectEvent->isUpdate());
        $this->assertFalse($selectEvent->isDelete());

        $this->assertTrue($insertEvent->isInsert());
        $this->assertTrue($updateEvent->isUpdate());
        $this->assertTrue($deleteEvent->isDelete());

        $this->assertSame('SELECT', $selectEvent->queryType);
        $this->assertSame('INSERT', $insertEvent->queryType);
        $this->assertSame('UPDATE', $updateEvent->queryType);
        $this->assertSame('DELETE', $deleteEvent->queryType);
        $this->assertSame('CREATE', $createEvent->queryType);
    }

    #[Test]
    public function query_type_detection_with_whitespace(): void
    {
        $event = new QueryExecuted('  SELECT   * FROM test', [], 0.0, null, false);

        $this->assertTrue($event->isSelect());
        $this->assertSame('SELECT', $event->queryType);
    }

    #[Test]
    public function query_type_detection_is_case_insensitive(): void
    {
        $select = new QueryExecuted('select * from test', [], 0.0, null, false);
        $insert = new QueryExecuted('insert into test values (1)', [], 0.0, null, false);
        $update = new QueryExecuted('update test set x = 1', [], 0.0, null, false);
        $delete = new QueryExecuted('delete from test', [], 0.0, null, false);

        $this->assertTrue($select->isSelect());
        $this->assertTrue($insert->isInsert());
        $this->assertTrue($update->isUpdate());
        $this->assertTrue($delete->isDelete());

        $this->assertSame('SELECT', $select->queryType);
    }

    #[Test]
    public function uses_full_table_scan_detection(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $database->fetch(new Query('SELECT * FROM test'));

        $event = $eventBus->dispatched[1];

        $this->assertTrue($event->usesFullTableScan());
    }

    #[Test]
    public function uses_full_table_scan_returns_false_for_non_select(): void
    {
        $event = new QueryExecuted('INSERT INTO test VALUES (1)', [], 0.0, null, false);

        $this->assertFalse($event->usesFullTableScan());
    }

    #[Test]
    public function get_rows_examined(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $database->execute(new Query('INSERT INTO test (id, name) VALUES (2, "test2")'));
        $database->fetch(new Query('SELECT * FROM test'));

        $event = $eventBus->dispatched[3];

        $this->assertGreaterThanOrEqual(0, $event->getRowsExamined());
    }

    #[Test]
    public function get_rows_examined_returns_zero_for_non_select(): void
    {
        $event = new QueryExecuted('INSERT INTO test VALUES (1)', [], 0.0, null, false);

        $this->assertSame(0, $event->getRowsExamined());
    }

    #[Test]
    public function uses_index_detection(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)'));
        $database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $database->fetch(new Query('SELECT * FROM test WHERE id = 1'));

        $event = $eventBus->dispatched[2];

        $this->assertTrue($event->usesIndex());
    }

    #[Test]
    public function uses_index_returns_false_for_full_table_scan(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $database->fetch(new Query('SELECT * FROM test'));

        $event = $eventBus->dispatched[1];

        $this->assertFalse($event->usesIndex());
    }

    #[Test]
    public function get_index_used(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)'));
        $database->execute(new Query('INSERT INTO test (id, name) VALUES (1, "test")'));
        $database->fetch(new Query('SELECT * FROM test WHERE id = 1'));

        $event = $eventBus->dispatched[2];

        $this->assertIsString($event->getIndexUsed());
        $this->assertNotEmpty($event->getIndexUsed());
    }

    #[Test]
    public function get_index_used_returns_null_when_no_index(): void
    {
        $eventBus = $this->createFakeEventBus();
        $database = $this->createDatabase($eventBus);

        $database->execute(new Query('CREATE TABLE test (id INTEGER, name TEXT)'));
        $database->fetch(new Query('SELECT * FROM test'));

        $event = $eventBus->dispatched[1];

        $this->assertNull($event->getIndexUsed());
    }
}
