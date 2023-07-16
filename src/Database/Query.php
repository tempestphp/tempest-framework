<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;

final readonly class Query
{
    public function __construct(
        public string $query,
        public array $bindings = [],
    ) {
    }

    public function execute(): Id
    {
        $pdo = get(PDO::class);

        $bindings = $this->resolveBindings();

        $pdo->prepare($this->query)->execute($bindings);

        return new Id($bindings['id'] ?? $pdo->lastInsertId());
    }

    public function fetch(): array
    {
        $pdo = get(PDO::class);

        $query = $pdo->prepare($this->query);

        $query->execute($this->resolveBindings());

        return $query->fetchAll(PDO::FETCH_NAMED);
    }

    public function fetchFirst(): ?array
    {
        return $this->fetch()[0] ?? null;
    }

    private function resolveBindings(): array
    {
        $bindings = [];

        foreach ($this->bindings as $key => $value) {
            if ($value instanceof Id) {
                $value = $value->id;
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }
}
