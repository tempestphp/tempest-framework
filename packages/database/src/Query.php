<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
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

        return isset($query->bindings[$this->primaryKeyColumn])
            ? new PrimaryKey($query->bindings[$this->primaryKeyColumn])
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
            $sql = str_replace('`', '', $sql);
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
