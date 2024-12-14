<?php

declare(strict_types=1);

namespace Tempest\Database;

use function Tempest\get;

final class Query
{
    public function __construct(
        public string $sql,
        public array $bindings = [],
    ) {
    }

    public function execute(mixed ...$bindings): Id
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        $database = $this->getDatabase();

        $query = $this->withBindings($bindings);

        $database->execute($query);

        return isset($query->bindings['id'])
            ? new Id($query->bindings['id'])
            : $database->getLastInsertId();
    }

    public function fetch(mixed ...$bindings): array
    {
        return $this->getDatabase()->fetch($this->withBindings($bindings));
    }

    public function fetchFirst(mixed ...$bindings): ?array
    {
        return $this->getDatabase()->fetchFirst($this->withBindings($bindings));
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function append(string $append): self
    {
        $this->sql .= PHP_EOL . $append;

        return $this;
    }

    public function withBindings(array $bindings): self
    {
        $clone = clone $this;

        $clone->bindings = [...$clone->bindings, ...$bindings];

        return $clone;
    }

    private function getDatabase(): Database
    {
        return get(Database::class);
    }
}
