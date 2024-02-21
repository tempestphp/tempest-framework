<?php

namespace Tempest\Database;

use PDO;
use Tempest\Interface\Database;

final readonly class GenericDatabase implements Database
{
    public function __construct(
        private PDO $pdo,
    ) {}

    public function execute(Query $query): void
    {
        $this->pdo
            ->prepare($query->getSql())
            ->execute($this->resolveBindings($query));
    }

    public function getLastInsertId(): Id
    {
        return new Id($this->pdo->lastInsertId());
    }

    public function fetch(Query $query): array
    {
        $pdoQuery = $this->pdo->prepare($query->getSql());

        $pdoQuery->execute($this->resolveBindings($query));

        return $pdoQuery->fetchAll(PDO::FETCH_NAMED);
    }

    public function fetchFirst(Query $query): ?array
    {
        return $this->fetch($query)[0] ?? null;
    }

    private function resolveBindings(Query $query): array
    {
        $bindings = [];

        foreach ($query->bindings as $key => $value) {
            if ($value instanceof Id) {
                $value = $value->id;
            }

            if ($value instanceof Query) {
                $value = $value->execute();
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }
}