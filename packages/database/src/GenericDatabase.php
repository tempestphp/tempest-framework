<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use PDOException;
use PDOStatement;
use Tempest\Database\Builder\QueryBuilders\BuildsQuery;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Transactions\TransactionManager;
use Tempest\EventBus\EventBus;
use Tempest\Mapper\Serializer;
use Tempest\Mapper\SerializerFactory;
use Tempest\Support\Str\ImmutableString;
use Throwable;
use UnitEnum;

// TODO: add DatabaseConnection to the Connection interface instead (4.x)
/** @property PDOConnection $connection */
final class GenericDatabase implements Database
{
    private ?PDOStatement $lastStatement = null;

    private ?Query $lastQuery = null;

    public DatabaseDialect $dialect {
        get => $this->connection->config->dialect;
    }

    public string|UnitEnum|null $tag {
        get => $this->connection->config->tag;
    }

    private DatabaseContext $context {
        get => new DatabaseContext(dialect: $this->dialect);
    }

    public function __construct(
        private(set) readonly Connection $connection,
        private(set) readonly TransactionManager $transactionManager,
        private(set) readonly SerializerFactory $serializerFactory,
        private readonly EventBus $eventBus,
    ) {}

    public function execute(BuildsQuery|Query $query): void
    {
        if ($query instanceof BuildsQuery) {
            $query = $query->build();
        }

        $this->runQuery($query, function (string $sql, array $bindings) use ($query): void {
            $statement = $this->connection->prepare($sql);
            $statement->execute($bindings);

            $this->lastStatement = $statement;
            $this->lastQuery = $query;
        });
    }

    public function getLastInsertId(): ?PrimaryKey
    {
        $sql = $this->lastQuery->compile();

        if (! $sql->trim()->startsWith('INSERT')) {
            return null;
        }

        if ($this->dialect === DatabaseDialect::POSTGRESQL) {
            $data = $this->lastStatement->fetch(PDO::FETCH_ASSOC);

            if (! $data) {
                return null;
            }

            if ($this->lastQuery->primaryKeyColumn && isset($data[$this->lastQuery->primaryKeyColumn])) {
                return PrimaryKey::tryFrom($data[$this->lastQuery->primaryKeyColumn]);
            }

            return null;
        }

        return PrimaryKey::tryFrom($this->connection->lastInsertId());
    }

    public function fetch(BuildsQuery|Query $query): array
    {
        if ($query instanceof BuildsQuery) {
            $query = $query->build();
        }

        return $this->runQuery($query, function (string $sql, array $bindings): array {
            $pdoQuery = $this->connection->prepare($sql);
            $pdoQuery->execute($bindings);

            return $pdoQuery->fetchAll(PDO::FETCH_NAMED);
        });
    }

    public function fetchFirst(BuildsQuery|Query $query): ?array
    {
        if ($query instanceof BuildsQuery) {
            $query = $query->build();
        }

        return $this->fetch($query)[0] ?? null;
    }

    public function withinTransaction(callable $callback): bool
    {
        $this->transactionManager->begin();

        try {
            $callback();

            $this->transactionManager->commit();
        } catch (Throwable) {
            $this->transactionManager->rollback();

            return false;
        }

        return true;
    }

    public function getRawSql(Query $query): ImmutableString
    {
        return new RawSql(
            dialect: $this->dialect,
            sql: (string) $query->compile(),
            bindings: $query->bindings,
            serializerFactory: $this->serializerFactory,
        )->toImmutableString();
    }

    private function resolveBindings(Query $query): array
    {
        $bindings = [];

        $serializerFactory = $this->serializerFactory->in($this->context);

        foreach ($query->bindings as $key => $value) {
            if ($value instanceof Query) {
                $value = $value->execute();
            } elseif (is_string($value) || is_numeric($value)) {
                // Keep value as is
            } elseif (($serializer = $serializerFactory->forValue($value)) instanceof Serializer) {
                $value = $serializer->serialize($value);
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }

    private function runQuery(Query $query, callable $runner): mixed
    {
        $bindings = $this->resolveBindings($query);
        $sql = $query->compile()->toString();
        $failed = true;
        $startTime = hrtime(true);

        try {
            $result = $runner($sql, $bindings);
            $failed = false;

            return $result;
        } catch (PDOException $pdoException) {
            throw new QueryWasInvalid($query, $bindings, $pdoException);
        } finally {
            try {
                $this->eventBus->dispatch(new QueryExecuted(
                    sql: $sql,
                    bindings: $bindings,
                    durationMs: (hrtime(true) - $startTime) / 1_000_000,
                    connectionName: $this->tag,
                    failed: $failed,
                ));
            } catch (Throwable) { // @mago-ignore lint:no-empty-catch-clause
            }
        }
    }
}
