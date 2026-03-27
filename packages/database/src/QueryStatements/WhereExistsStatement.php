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

        if ($this->useCount) {
            return "(SELECT COUNT(*) FROM {$this->relatedTable} WHERE {$whereClause}) {$this->operator->value} {$this->count}";
        }

        $keyword = $this->negate
            ? 'NOT EXISTS'
            : 'EXISTS';

        return "{$keyword} (SELECT 1 FROM {$this->relatedTable} WHERE {$whereClause})";
    }
}
