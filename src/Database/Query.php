<?php

namespace Tempest\Database;

use PDO;

final readonly class Query
{
    public function __construct(
        private string $query,
        private array $bindings = [],
    ) {}

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

        return $pdo->prepare($this->query)->fetchAll($this->resolveBindings());
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