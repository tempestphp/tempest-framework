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
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\GenericEventBus;
use Tempest\Mapper\SerializerFactory;

final class QueryExecutionBench
{
    private GenericDatabase $database;

    public function setUp(): void
    {
        $container = new GenericContainer();

        $config = new SQLiteConfig(path: ':memory:');
        $connection = new PDOConnection($config);
        $connection->connect();

        $this->database = new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
            new SerializerFactory($container),
            new GenericEventBus($container, new EventBusConfig()),
        );

        $container->singleton(Database::class, $this->database);
        GenericContainer::setInstance($container);

        $this->database->execute(new Query('CREATE TABLE IF NOT EXISTS bench (id INTEGER PRIMARY KEY, name TEXT, value TEXT)'));

        foreach (range(1, 100) as $i) {
            $this->database->execute(new Query(
                'INSERT INTO bench (name, value) VALUES (:name, :value)',
                [':name' => "item_{$i}", ':value' => "value_{$i}"],
            ));
        }
    }

    #[BeforeMethods('setUp')]
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

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchFetchAll(): void
    {
        $this->database->fetch(new Query('SELECT * FROM bench'));
    }

    #[BeforeMethods('setUp')]
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
}
