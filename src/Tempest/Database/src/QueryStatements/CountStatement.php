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
            sprintf('SELECT COUNT(%s)', $this->countArgument),
            sprintf('FROM `%s`', $this->table->name),
        ]);

        if ($this->where->isNotEmpty()) {
            $query[] = 'WHERE ' . $this->where
                ->map(fn (WhereStatement $where) => $where->compile($dialect))
                ->implode(PHP_EOL);
        }

        return $query->implode(PHP_EOL);
    }
}
