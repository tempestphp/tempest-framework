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
            $eventBus,
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
}
