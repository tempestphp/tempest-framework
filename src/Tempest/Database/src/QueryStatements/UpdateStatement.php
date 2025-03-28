<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\EmptyUpdateStatement;
use Tempest\Database\Exceptions\InvalidUpdateStatement;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;

final class UpdateStatement implements QueryStatement
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
            throw new InvalidUpdateStatement();
        }

        $query = arr([
            sprintf('UPDATE `%s`', $this->table->name),
        ]);

        if ($this->values->isEmpty()) {
            throw new EmptyUpdateStatement();
        }

        $query[] = 'SET ' . $this->values
            ->map(fn (mixed $value, mixed $key) => sprintf("`{$key}` = ?"))
            ->implode(', ');

        if ($this->where->isNotEmpty()) {
            $query[] = 'WHERE ' . $this->where
                ->map(fn (WhereStatement $where) => $where->compile($dialect))
                ->implode(PHP_EOL);
        }

        return $query->implode(PHP_EOL);
    }
}
