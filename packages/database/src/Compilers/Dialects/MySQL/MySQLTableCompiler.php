<?php

namespace Tempest\Database\Compilers\Dialects\MySQL;

use Tempest\Database\Columns\Column;
use Tempest\Database\Compilers\TableCompiler;
use Tempest\Database\TableDefinitions\CreateTable;
use Tempest\Database\TableDefinitions\TableDefinition;

final class MySQLTableCompiler implements TableCompiler
{
    public function __construct(private readonly MySQLColumnCompiler $columnCompiler)
    {}

    public function compile(TableDefinition $tableDefinition): string
    {
        return match(true) {
            $tableDefinition instanceof CreateTable => $this->compileCreateTable($tableDefinition),
        };
    }

    private function compileCreateTable(CreateTable $createTable): string
    {
        if (empty($createTable->columns)) {
            // @todo(aidan-casey): Real exceptions.
            throw new \InvalidArgumentException('Table must have at least one column');
        }

        // @todo(aidan-casey): Guard against duplicate column names.

        $columns = array_map(
            fn (Column $column) => $this->columnCompiler->compile($column),
            $createTable->columns
        );

        $columns = implode(",\n", $columns);
        $columns = '    ' . str_replace("\n", "\n    ", $columns);

        return <<<SQL
        CREATE TABLE {$this->quote($createTable->name)} (
        {$columns}
        );
        SQL;
    }

    private function quote(string $value): string
    {
        return '`' . str_replace('`', '``', $value) . '`';
    }
}