<?php

namespace Tempest\Database\TableDefinitions;

use Tempest\Database\Columns\Column;
use Tempest\Database\Indexes\Index;

final readonly class CreateTable implements TableDefinition
{
    /**
     * @param list<Column> $columns
     * @param list<Index> $indexes
     */
    public function __construct(
        public string $name,
        public array $columns = [],
        public array $indexes = [],
    ) {}

    public function addColumn(Column $column): self
    {
        return $this->addColumns($column);
    }

    public function addColumns(Column ...$columns): self
    {
        return clone($this, [
            'columns' => [...$this->columns, ...$columns],
        ]);
    }

    public function addIndex(Index $index): self
    {
        return $this->addIndexes($index);
    }

    public function addIndexes(Index ...$indexes): self
    {
        return clone($this, [
            'columns' => [...$this->columns, ...$indexes],
        ]);
    }
}