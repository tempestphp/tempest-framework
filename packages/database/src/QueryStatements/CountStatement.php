<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;

final class CountStatement implements QueryStatement
{
    public bool $distinct = false;

    public function __construct(
        public readonly TableDefinition $table,
        public ?string $column = null,
        public ImmutableArray $where = new ImmutableArray(),
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $countField = new FieldStatement(sprintf(
            'COUNT(%s) AS %s',
            $this->getCountArgument(),
            $this->getKey(),
        ));

        $query = arr([
            sprintf('SELECT %s', $countField->compile($dialect)),
            sprintf('FROM `%s`', $this->table->name),
        ]);

        if ($this->where->isNotEmpty()) {
            $query[] = 'WHERE ' . $this->where
                ->map(fn (WhereStatement $where) => $where->compile($dialect))
                ->implode(PHP_EOL);
        }

        return $query->implode(PHP_EOL);
    }

    public function getCountArgument(): string
    {
        return $this->column === null || $this->column === '*'
            ? '*'
            : sprintf(
                '%s`%s`',
                $this->distinct ? 'DISTINCT ' : '',
                $this->column,
            );
    }

    public function getKey(): string
    {
        return 'count';
    }
}
