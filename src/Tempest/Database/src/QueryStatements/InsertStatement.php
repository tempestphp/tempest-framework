<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\InsertColumnMismatch;
use Tempest\Database\QueryStatement;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;

final class InsertStatement implements QueryStatement
{
    public function __construct(
        public readonly TableDefinition $table,
        /** @var ImmutableArray<array-key, array|ImmutableArray> */
        public ImmutableArray $entries = new ImmutableArray(),
    ) {}

    public function addEntry(array|ImmutableArray $entry): self
    {
        $this->entries[] = $entry;

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $columns = arr($this->entries->first())->keys();

        $entryPlaceholders = $this->entries
            ->map(function (array|ImmutableArray $row) use ($columns) {
                $row = arr($row);

                $rowColumns = $row->keys();

                if (! $columns->equals($rowColumns)) {
                    throw new InsertColumnMismatch($columns, $rowColumns);
                }

                return sprintf(
                    '(%s)',
                    $row->map(fn () => '?')->implode(', '),
                );
            })
            ->implode(', ');

        return sprintf(
            <<<SQL
            INSERT INTO %s (%s)
            VALUES %s
            SQL,
            $this->table,
            $columns->map(fn (string $column) => "`{$column}`")->implode(', '),
            $entryPlaceholders,
        );
    }
}
