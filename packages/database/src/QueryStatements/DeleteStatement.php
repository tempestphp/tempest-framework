<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\DeleteStatementWasInvalid;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;

final class DeleteStatement implements QueryStatement, HasWhereStatements
{
    public function __construct(
        public readonly TableDefinition $table,
        public ImmutableArray $where = new ImmutableArray(),
        public bool $allowAll = false,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->allowAll === false && $this->where->isEmpty()) {
            throw new DeleteStatementWasInvalid();
        }

        $query = arr([
            sprintf('DELETE FROM `%s`', $this->table->name),
        ]);

        if ($this->where->isNotEmpty()) {
            $query[] = 'WHERE ' . $this->where
                ->map(fn (QueryStatement $where) => $where->compile($dialect))
                ->filter(fn (string $compiled) => $compiled !== '')
                ->implode(' ');
        }

        return $query->implode(' ');
    }
}
