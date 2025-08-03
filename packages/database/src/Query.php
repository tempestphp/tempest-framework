<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Support\Str\ImmutableString;

use function Tempest\get;

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

    /**
     * Returns the SQL statement without the bindings.
     */
    public function toSql(): ImmutableString
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
        $sql = $this->toSql();
        $resolvedBindings = $this->resolveBindingsForDisplay();

        if (! array_is_list($resolvedBindings)) {
            return $this->replaceNamedBindings((string) $sql, $resolvedBindings);
        }

        return $this->replacePositionalBindings((string) $sql, array_values($resolvedBindings));
    }

    private function replaceNamedBindings(string $sql, array $bindings): ImmutableString
    {
        foreach ($bindings as $key => $value) {
            $placeholder = ':' . $key;
            $formattedValue = $this->formatValueForSql($value);
            $sql = str_replace($placeholder, $formattedValue, $sql);
        }

        return new ImmutableString($sql);
    }

    private function replacePositionalBindings(string $sql, array $bindings): ImmutableString
    {
        $bindingIndex = 0;
        $result = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            if ($sql[$i] === '?' && $bindingIndex < count($bindings)) {
                $value = $bindings[$bindingIndex];
                $result .= $this->formatValueForSql($value);
                $bindingIndex++;
            } else {
                $result .= $sql[$i];
            }
        }

        return new ImmutableString($result);
    }

    private function resolveBindingsForDisplay(): array
    {
        $bindings = [];

        foreach ($this->bindings as $key => $value) {
            if (is_bool($value)) {
                $value = match ($this->dialect) {
                    DatabaseDialect::POSTGRESQL => $value ? 'true' : 'false',
                    default => $value ? '1' : '0',
                };
            }

            if ($value instanceof Query) {
                $value = '(' . $value->toRawSql() . ')';
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }

    private function formatValueForSql(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_string($value)) {
            if (str_starts_with($value, '(') && str_ends_with($value, ')')) {
                return $value;
            }

            return "'" . str_replace("'", "''", $value) . "'";
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return "'" . str_replace("'", "''", (string) $value) . "'";
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
