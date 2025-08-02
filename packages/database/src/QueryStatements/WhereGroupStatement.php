<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

final readonly class WhereGroupStatement implements QueryStatement
{
    /**
     * @param ImmutableArray<WhereStatement> $conditions
     */
    public function __construct(
        public ImmutableArray $conditions = new ImmutableArray(),
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->conditions->isEmpty()) {
            return '';
        }

        $compiled = $this->conditions
            ->map(fn (QueryStatement $condition) => $condition->compile($dialect))
            ->filter(fn (string $condition) => $condition !== '');

        if ($compiled->isEmpty()) {
            return '';
        }

        if ($compiled->count() === 1) {
            return $compiled[0];
        }

        $joined = $compiled->implode(' ');

        return "({$joined})";
    }

    public function addCondition(QueryStatement $condition): self
    {
        return new self($this->conditions->append($condition));
    }

    public function addGroup(WhereGroupStatement $group): self
    {
        return new self($this->conditions->append($group));
    }
}
