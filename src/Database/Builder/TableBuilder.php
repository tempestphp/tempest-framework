<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Tempest\Interfaces\TableRow;

final class TableBuilder
{
    private ?TableName $tableName = null;

    private array $rows = [];

    private TableBuilderAction $action = TableBuilderAction::CREATE;

    private bool $ifNotExists = false;

    public function name(TableName $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function add(TableRow $row): self
    {
        $this->rows[] = $row;

        return $this;
    }

    public function ifNotExists(): self
    {
        $this->ifNotExists = true;

        return $this;
    }

    public function create(): self
    {
        $this->action = TableBuilderAction::CREATE;

        return $this;
    }

    public function drop(): self
    {
        $this->action = TableBuilderAction::DROP;

        return $this;
    }

    public function alter(): self
    {
        $this->action = TableBuilderAction::ALTER;

        return $this;
    }

    public function getQuery(): string
    {
        $statements = [];

        if ($this->action === TableBuilderAction::CREATE) {
            $statements[] = "CREATE TABLE";

            if ($this->ifNotExists()) {
                $statements[] = 'IF NOT EXISTS';
            }

            $statements[] = "{$this->tableName} (";

            $statements[] = implode(
                ',' . PHP_EOL,
                array_map(
                    fn (TableRow $row) => $row->getDefinition(),
                    $this->rows,
                ),
            );

            $statements[] = ');';
        } elseif ($this->action === TableBuilderAction::DROP) {
            $statements[] = "DROP TABLE {$this->tableName};";
        }

        return implode(PHP_EOL, $statements);
    }
}
