<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Database;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Container\GenericContainer;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryExecuted;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\GenericEventBus;
use Tempest\Mapper\SerializerFactory;

final class QueryExecutionBench
{
    private GenericDatabase $database;

    private function createDatabase(GenericEventBus $eventBus): GenericDatabase
    {
        $container = new GenericContainer();

        $config = new SQLiteConfig(path: ':memory:');
        $connection = new PDOConnection($config);
        $connection->connect();

        $database = new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
            new SerializerFactory($container),
            $eventBus,
        );

        $container->singleton(Database::class, $database);
        GenericContainer::setInstance($container);

        return $database;
    }

    private function seedTable(): void
    {
        $this->database->execute(new Query('CREATE TABLE IF NOT EXISTS bench (id INTEGER PRIMARY KEY, name TEXT, value TEXT)'));

        foreach (range(1, 100) as $i) {
            $this->database->execute(new Query(
                'INSERT INTO bench (name, value) VALUES (:name, :value)',
                [':name' => "item_{$i}", ':value' => "value_{$i}"],
            ));
        }
    }

    public function setUpWithoutListener(): void
    {
        $container = new GenericContainer();
        $eventBus = new GenericEventBus($container, new EventBusConfig());

        $this->database = $this->createDatabase($eventBus);
        $this->seedTable();
    }

    public function setUpWithListener(): void
    {
        $container = new GenericContainer();
        $eventBus = new GenericEventBus($container, new EventBusConfig());
        $eventBus->listen(function (QueryExecuted $event): void {});

        $this->database = $this->createDatabase($eventBus);
        $this->seedTable();
    }

    #[BeforeMethods('setUpWithoutListener')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchExecuteInsert(): void
    {
        $this->database->execute(new Query(
            'INSERT INTO bench (name, value) VALUES (:name, :value)',
            [':name' => 'bench_item', ':value' => 'bench_value'],
        ));
    }

    #[BeforeMethods('setUpWithListener')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchExecuteInsertWithListener(): void
    {
        $this->database->execute(new Query(
            'INSERT INTO bench (name, value) VALUES (:name, :value)',
            [':name' => 'bench_item', ':value' => 'bench_value'],
        ));
    }

    #[BeforeMethods('setUpWithoutListener')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchFetchAll(): void
    {
        $this->database->fetch(new Query('SELECT * FROM bench'));
    }

    #[BeforeMethods('setUpWithListener')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchFetchAllWithListener(): void
    {
        $this->database->fetch(new Query('SELECT * FROM bench'));
    }

    #[BeforeMethods('setUpWithoutListener')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchFetchWithBindings(): void
    {
        $this->database->fetch(new Query(
            'SELECT * FROM bench WHERE name = :name',
            [':name' => 'item_50'],
        ));
    }

    #[BeforeMethods('setUpWithListener')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchFetchWithBindingsAndListener(): void
    {
        $this->database->fetch(new Query(
            'SELECT * FROM bench WHERE name = :name',
            [':name' => 'item_50'],
        ));
    }
}
