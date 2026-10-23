<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\InsertStatement;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Container\get;

/**
 * A database query that can be executed.
 */
final class Query
{
    use OnDatabase;

    private Database $database {
        get => get(Database::class, $this->onDatabase);
    }

    private DatabaseDialect $dialect {
        get => $this->database->dialect;
    }

    public function __construct(
        public string|QueryStatement $sql,
        public array $bindings = [],
        /** @var \Closure[] $executeAfter */
        public array $executeAfter = [],
        public ?string $primaryKeyColumn = null,
    ) {}

    public function execute(mixed ...$bindings): ?PrimaryKey
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        $database = $this->database;

        $query = $this->withBindings($bindings);

        $database->execute($query);

        // TODO: add support for "after" queries to attach hasMany relations

        if (! $this->primaryKeyColumn) {
            return null;
        }

        if (isset($query->bindings[$this->primaryKeyColumn])) {
            return new PrimaryKey($query->bindings[$this->primaryKeyColumn]);
        }

        // Insert bindings are positional; resolve the primary key through its column position.
        $positionalValue = $this->resolvePositionalPrimaryKeyBinding($query);

        return $positionalValue !== null
            ? new PrimaryKey($positionalValue)
            : $database->getLastInsertId();
    }

    private function resolvePositionalPrimaryKeyBinding(Query $query): mixed
    {
        if (! $this->sql instanceof InsertStatement) {
            return null;
        }

        $firstEntry = $this->sql->entries->first();

        if ($firstEntry instanceof ImmutableArray) {
            $firstEntry = $firstEntry->toArray();
        }

        if (! is_array($firstEntry)) {
            return null;
        }

        $index = array_search($this->primaryKeyColumn, array_keys($firstEntry), strict: true);

        if ($index === false) {
            return null;
        }

        $value = $query->bindings[$index] ?? null;

        // 0 is treated as "auto-increment" by MySQL/PostgreSQL — not a real id.
        if ($value === 0 || $value === null) {
            return null;
        }

        return $value;
    }

    public function fetch(mixed ...$bindings): array
    {
        return $this->database->fetch($this->withBindings($bindings));
    }

    public function fetchFirst(mixed ...$bindings): ?array
    {
        return $this->database->fetchFirst($this->withBindings($bindings));
    }

    /**
     * Compile the query to a SQL statement without the bindings.
     */
    public function compile(): ImmutableString
    {
        $sql = $this->sql;
        $dialect = $this->dialect;

        if ($sql instanceof QueryStatement) {
            $sql = $sql->compile($dialect);
        }

        if ($dialect === DatabaseDialect::POSTGRESQL) {
            $sql = str_replace('`', '"', $sql);
        }

        return new ImmutableString($sql);
    }

    /**
     * Returns the SQL statement with bindings. This method may generate syntax errors, it is not recommended to use it other than for debugging.
     */
    public function toRawSql(): ImmutableString
    {
        return $this->database->getRawSql($this);
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
