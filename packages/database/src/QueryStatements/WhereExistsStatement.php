<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\str;

final readonly class WhereExistsStatement implements QueryStatement
{
    public function __construct(
        public string $relatedTable,
        public string $relatedModelName,
        public string $condition,
        public ?JoinStatement $joinStatement = null,
        private ImmutableArray $innerWheres = new ImmutableArray(),
        private bool $negate = false,
        private bool $useCount = false,
        private WhereOperator $operator = WhereOperator::GREATER_THAN_OR_EQUAL,
        private int $count = 1,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $whereClause = str(string: $this->condition);

        if ($this->innerWheres->isNotEmpty()) {
            $compiled = $this->innerWheres
                ->map(map: fn (
                    QueryStatement $where,
                ) => $where->compile(dialect: $dialect))
                ->filter(
                    filter: fn (
                        string $compiled,
                    ) => $compiled !== '',
                )
                ->implode(glue: ' ')
                ->toString();

            if ($compiled !== '') {
                $whereClause = $whereClause->append(suffix: " AND {$compiled}");
            }
        }

        $fromClause = $this->relatedTable;

        if ($this->joinStatement instanceof JoinStatement && $this->innerWheres->isNotEmpty()) {
            $fromClause .= ' ' . $this->joinStatement->compile(dialect: $dialect);
        }

        if ($this->useCount) {
            $statement = "(SELECT COUNT(*) FROM {$fromClause} WHERE {$whereClause}) {$this->operator->value} {$this->count}";
        } else {
            $keyword = $this->negate
                ? 'NOT EXISTS'
                : 'EXISTS';
            $statement = "{$keyword} (SELECT 1 FROM {$fromClause} WHERE {$whereClause})";
        }

        return match ($dialect) {
            DatabaseDialect::POSTGRESQL => str_replace('`', '"', $statement),
            DatabaseDialect::SQLITE => str_replace('`', '', $statement),
            default => $statement,
        };
    }
}
