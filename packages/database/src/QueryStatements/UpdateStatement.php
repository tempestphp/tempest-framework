<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\UpdateStatementWasEmpty;
use Tempest\Database\Exceptions\UpdateStatementWasInvalid;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;

final class UpdateStatement implements QueryStatement, HasWhereStatements
{
    public function __construct(
        public readonly TableDefinition $table,
        public ImmutableArray $values = new ImmutableArray(),
        public ImmutableArray $where = new ImmutableArray(),
        public bool $allowAll = false,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->allowAll === false && $this->where->isEmpty()) {
            throw new UpdateStatementWasInvalid();
        }

        $query = arr([
            sprintf('UPDATE `%s`', $this->table->name),
        ]);

        if ($this->values->isEmpty()) {
            throw new UpdateStatementWasEmpty();
        }

        $query[] = 'SET ' . $this->values
            ->map(fn (mixed $_, mixed $key) => sprintf("`{$key}` = ?"))
            ->implode(', ');

        if ($this->where->isNotEmpty()) {
            $query[] = 'WHERE ' . $this->where
                ->map(fn (WhereStatement|WhereGroupStatement $where) => $where->compile($dialect))
                ->filter(fn (string $compiled) => $compiled !== '')
                ->implode(' ');
        }

        return $query->implode(' ');
    }
}
