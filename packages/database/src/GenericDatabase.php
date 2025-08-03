<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use PDOException;
use PDOStatement;
use Tempest\Database\Builder\QueryBuilders\BuildsQuery;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Transactions\TransactionManager;
use Tempest\Mapper\SerializerFactory;
use Throwable;
use UnitEnum;

final class GenericDatabase implements Database
{
    private ?PDOStatement $lastStatement = null;
    private ?Query $lastQuery = null;

    public DatabaseDialect $dialect {
        get => $this->connection->config->dialect;
    }

    public null|string|UnitEnum $tag {
        get => $this->connection->config->tag;
    }

    public function __construct(
        private(set) readonly Connection $connection,
        private(set) readonly TransactionManager $transactionManager,
        private(set) readonly SerializerFactory $serializerFactory,
    ) {}

    public function execute(BuildsQuery|Query $query): void
    {
        if ($query instanceof BuildsQuery) {
            $query = $query->build();
        }

        $bindings = $this->resolveBindings($query);

        try {
            $statement = $this->connection->prepare($query->toSql());

            $statement->execute($bindings);

            $this->lastStatement = $statement;
            $this->lastQuery = $query;
        } catch (PDOException $pdoException) {
            throw new QueryWasInvalid($query, $bindings, $pdoException);
        }
    }

    public function getLastInsertId(): ?Id
    {
        $sql = $this->lastQuery->toSql();

        // TODO: properly determine whether a query is an insert or not
        if (! str_starts_with($sql, 'INSERT')) {
            return null;
        }

        if ($this->dialect === DatabaseDialect::POSTGRESQL) {
            $data = $this->lastStatement->fetch(PDO::FETCH_ASSOC);
            $lastInsertId = $data['id'] ?? null;
        } else {
            $lastInsertId = $this->connection->lastInsertId();
        }

        return Id::tryFrom($lastInsertId);
    }

    public function fetch(BuildsQuery|Query $query): array
    {
        if ($query instanceof BuildsQuery) {
            $query = $query->build();
        }

        $bindings = $this->resolveBindings($query);

        try {
            $pdoQuery = $this->connection->prepare($query->toSql());

            $pdoQuery->execute($bindings);

            return $pdoQuery->fetchAll(PDO::FETCH_NAMED);
        } catch (PDOException $pdoException) {
            throw new QueryWasInvalid($query, $bindings, $pdoException);
        }
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

    private function resolveBindings(Query $query): array
    {
        $bindings = [];

        foreach ($query->bindings as $key => $value) {
            // Database handle booleans differently. We might need a database-aware serializer at some point.
            if (is_bool($value)) {
                $value = match ($this->dialect) {
                    DatabaseDialect::POSTGRESQL => $value ? 'true' : 'false',
                    default => $value ? '1' : '0',
                };
            }

            if ($value instanceof Query) {
                $value = $value->execute();
            } elseif ($serializer = $this->serializerFactory->forValue($value)) {
                $value = $serializer->serialize($value);
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }
}
