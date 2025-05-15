<?php

namespace Tempest\Database\QueryStatements;

use Stringable;
use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

final class SelectStatement implements QueryStatement
{
    public function __construct(
        public TableDefinition $table,
        public ImmutableArray $fields = new ImmutableArray(),
        public ImmutableArray $join = new ImmutableArray(),
        public ImmutableArray $where = new ImmutableArray(),
        public ImmutableArray $orderBy = new ImmutableArray(),
        public ImmutableArray $groupBy = new ImmutableArray(),
        public ImmutableArray $having = new ImmutableArray(),
        public ?int $limit = null,
        public ?int $offset = null,
        public ImmutableArray $raw = new ImmutableArray(),
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $columns = $this->fields->isEmpty()
            ? '*'
            : $this->fields
                ->map(function (string|Stringable|FieldStatement $field) use ($dialect) {
                    if (! ($field instanceof FieldStatement)) {
                        $field = new FieldStatement($field);
                    }

                    return $field->compile($dialect);
                })
                ->implode(', ');

        $query = new ImmutableArray([
            'SELECT ' . $columns,
            'FROM ' . $this->table,
        ]);

        if ($this->join->isNotEmpty()) {
            $query[] = $this->join
                ->map(fn (JoinStatement $join) => $join->compile($dialect))
                ->implode(PHP_EOL);
        }

        if ($this->where->isNotEmpty()) {
            $query[] = 'WHERE ' . $this->where
                ->map(fn (WhereStatement $where) => $where->compile($dialect))
                ->implode(PHP_EOL);
        }

        if ($this->orderBy->isNotEmpty()) {
            $query[] = 'ORDER BY ' . $this->orderBy
                ->map(fn (OrderByStatement $orderBy) => $orderBy->compile($dialect))
                ->implode(', ');
        }

        if ($this->groupBy->isNotEmpty()) {
            $query[] = 'GROUP BY ' . $this->groupBy
                ->map(fn (GroupByStatement $groupBy) => $groupBy->compile($dialect))
                ->implode(', ');
        }

        if ($this->having->isNotEmpty()) {
            $query[] = 'HAVING ' . $this->having
                ->map(fn (HavingStatement $having) => $having->compile($dialect))
                ->implode(PHP_EOL);
        }

        if ($this->limit !== null) {
            $query[] = 'LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $query[] = 'OFFSET ' . $this->offset;
        }

        if ($this->raw->isNotEmpty()) {
            $query[] = $this->raw
                ->map(fn (RawStatement $raw) => $raw->compile($dialect))
                ->implode(PHP_EOL);
        }

        $compiled = $query->implode(PHP_EOL);

        /* TODO: this should be improved.
         * More specifically, \Tempest\Database\Builder\FieldDefinition should be aware of the dialect,
         * or the whole ORM should be refactored to use \Tempest\Database\QueryStatements\FieldStatement*/
        //        if ($dialect === DatabaseDialect::SQLITE) {
        //            $compiled = $compiled->replace('`', '');
        //        }

        return $compiled;
    }
}
