<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;

final class CountStatement implements QueryStatement
{
    public readonly string $countArgument;

    public ?string $alias = null;

    public function __construct(
        public readonly TableDefinition $table,
        public ?string $column = null,
        public ImmutableArray $where = new ImmutableArray(),
    ) {
        $this->countArgument = $this->column === null
            ? '*'
            : "`{$this->column}`";
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $query = arr([
            sprintf(
                'SELECT COUNT(%s)%s',
                $this->countArgument,
                $this->alias ? " AS `{$this->alias}`" : '',
            ),
            sprintf('FROM `%s`', $this->table->name),
        ]);

        if ($this->where->isNotEmpty()) {
            $query[] = 'WHERE ' . $this->where
                ->map(fn (WhereStatement $where) => $where->compile($dialect))
                ->implode(PHP_EOL);
        }

        return $query->implode(PHP_EOL);
    }

    public function getKey(): string
    {
        if ($this->alias !== null) {
            return $this->alias;
        }

        return "COUNT({$this->countArgument})";
    }
}
