<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;

use function Tempest\get;

final class Query
{
    use UsesDatabase;

    private Database $database {
        get => get(Database::class, $this->useDatabase);
    }

    private DatabaseDialect $dialect {
        get => get(DatabaseDialect::class, $this->useDatabase);
    }

    public function __construct(
        public string|QueryStatement $sql,
        public array $bindings = [],
        /** @var \Closure[] $executeAfter */
        public array $executeAfter = [],
    ) {}

    public function execute(mixed ...$bindings): ?Id
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        $database = $this->database;

        $query = $this->withBindings($bindings);

        $database->execute($query);

        // TODO: add support for "after" queries to attach hasMany relations

        return isset($query->bindings['id'])
            ? new Id($query->bindings['id'])
            : $database->getLastInsertId();
    }

    public function fetch(mixed ...$bindings): array
    {
        return $this->database->fetch($this->withBindings($bindings));
    }

    public function fetchFirst(mixed ...$bindings): ?array
    {
        return $this->database->fetchFirst($this->withBindings($bindings));
    }

    public function toSql(): string
    {
        $sql = $this->sql;

        $dialect = $this->dialect;

        if ($sql instanceof QueryStatement) {
            $sql = $sql->compile($dialect);
        }

        if ($dialect === DatabaseDialect::POSTGRESQL) {
            $sql = str_replace('`', '', $sql);
        }

        return $sql;
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
}
