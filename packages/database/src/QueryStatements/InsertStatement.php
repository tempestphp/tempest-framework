<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\InsertColumnsMismatched;
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
                    throw new InsertColumnsMismatched($columns, $rowColumns);
                }

                return sprintf(
                    '(%s)',
                    $row->map(fn () => '?')->implode(', '),
                );
            })
            ->implode(', ');

        if ($columns->isEmpty()) {
            $sql = match ($dialect) {
                DatabaseDialect::MYSQL => sprintf('INSERT INTO %s () VALUES ()', $this->table),
                default => sprintf('INSERT INTO %s DEFAULT VALUES', $this->table),
            };
        } else {
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                $this->table,
                $columns->map(fn (string $column) => "`{$column}`")->implode(', '),
                $entryPlaceholders,
            );
        }

        if ($dialect === DatabaseDialect::POSTGRESQL) {
            $sql .= ' RETURNING *';
        }

        return $sql;
    }
}
