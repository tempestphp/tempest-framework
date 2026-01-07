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
use Tempest\Support\Str\ImmutableString;
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

    private DatabaseContext $context {
        get => new DatabaseContext(dialect: $this->dialect);
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
            $statement = $this->connection->prepare($query->compile()->toString());
            $statement->execute($bindings);

            $this->lastStatement = $statement;
            $this->lastQuery = $query;
        } catch (PDOException $pdoException) {
            throw new QueryWasInvalid($query, $bindings, $pdoException);
        }
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

        $bindings = $this->resolveBindings($query);

        try {
            $pdoQuery = $this->connection->prepare($query->compile()->toString());
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

        foreach ($query->bindings as $key => $value) {
            if ($value instanceof Query) {
                $value = $value->execute();
            } elseif ($serializer = $this->serializerFactory->in($this->context)->forValue($value)) {
                $value = $serializer->serialize($value);
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }
}
