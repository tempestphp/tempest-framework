<?php

declare(strict_types=1);

namespace Tempest\Database;

use BackedEnum;
use DateTimeInterface;
use PDO;
use PDOException;
use PDOStatement;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Exceptions\QueryException;
use Tempest\Database\Transactions\TransactionManager;
use Throwable;

final class GenericDatabase implements Database
{
    private PDOStatement|null $lastStatement = null;
    private Query|null $lastQuery = null;

    public function __construct(
        private(set) readonly Connection $connection,
        private(set) readonly TransactionManager $transactionManager,
        private(set) readonly DatabaseDialect $dialect,
    ) {}

    public function execute(Query $query): void
    {
        $bindings = $this->resolveBindings($query);

        try {
            foreach (explode(';', $query->toSql()) as $sql) {
                if (! $sql) {
                    continue;
                }

                $statement = $this->connection->prepare($sql . ';');

                $statement->execute($bindings);

                $this->lastStatement = $statement;
                $this->lastQuery = $query;
            }
        } catch (PDOException $pdoException) {
            throw new QueryException($query, $bindings, $pdoException);
        }
    }

    public function getLastInsertId(): Id|null
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

    public function fetch(Query $query): array
    {
        $pdoQuery = $this->connection->prepare($query->toSql());

        $pdoQuery->execute($this->resolveBindings($query));

        return $pdoQuery->fetchAll(PDO::FETCH_NAMED);
    }

    public function fetchFirst(Query $query): ?array
    {
        return $this->fetch($query)[0] ?? null;
    }

    public function withinTransaction(callable $callback): bool
    {
        $this->transactionManager->begin();

        try {
            $callback();

            $this->transactionManager->commit();
        } catch (PDOException) {
            return false;
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
            // TODO: this should be handled by serializers (except the Query)
            if ($value instanceof Id) {
                $value = $value->id;
            }

            if ($value instanceof Query) {
                $value = $value->execute();
            }

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }
}
